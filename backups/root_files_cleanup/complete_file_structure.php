<?php
/**
 * 完善文件结构脚本
 * 这个脚本会分析项目结构，并为只有基本结构的文件生成更完整的内容
 */

// 设置脚本最大执行时间
set_time_limit(600);

// 源代码目录
$srcDir = __DIR__ . '/src';

// 统计信息
$stats = [
    'files_scanned' => 0,
    'files_enhanced' => 0,
    'directories_processed' => 0,
    'errors' => 0
];

// 模板库
$templates = [
    'controller' => [
        'namespace_pattern' => 'AlingAi\\Controllers',
        'template' => <<<'EOT'
<?php

declare(strict_types=1);

namespace {{NAMESPACE}};

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use AlingAi\Core\BaseController;
use AlingAi\Services\LoggingService;

/**
 * {{CLASS_NAME}}
 * 
 * {{CLASS_DESCRIPTION}}
 */
class {{CLASS_NAME}} extends BaseController
{
    private LoggingService $logger;
    
    /**
     * 构造函数
     */
    public function __construct(LoggingService $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * 处理请求
     */
    public function handleRequest(Request $request, Response $response): Response
    {
        $this->logger->info('{{CLASS_NAME}} 处理请求');
        
        $response->getBody()->write(json_encode([
            'status' => 'success',
            'message' => '{{CLASS_NAME}} 响应成功'
        ]));
        
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * 获取数据
     */
    public function getData(Request $request, Response $response): Response
    {
        $this->logger->info('{{CLASS_NAME}} 获取数据');
        
        $response->getBody()->write(json_encode([
            'status' => 'success',
            'data' => [
                'items' => []
            ]
        ]));
        
        return $response->withHeader('Content-Type', 'application/json');
    }
}
EOT
    ],
    'service' => [
        'namespace_pattern' => 'AlingAi\\Services',
        'template' => <<<'EOT'
<?php

declare(strict_types=1);

namespace {{NAMESPACE}};

use Psr\Log\LoggerInterface;

/**
 * {{CLASS_NAME}}
 * 
 * {{CLASS_DESCRIPTION}}
 */
class {{CLASS_NAME}}
{
    private LoggerInterface $logger;
    
    /**
     * 构造函数
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * 初始化服务
     */
    public function initialize(): bool
    {
        $this->logger->info('{{CLASS_NAME}} 初始化');
        return true;
    }
    
    /**
     * 执行服务操作
     */
    public function execute(array $params = []): array
    {
        $this->logger->info('{{CLASS_NAME}} 执行操作', ['params' => $params]);
        
        return [
            'status' => 'success',
            'message' => '操作成功执行'
        ];
    }
}
EOT
    ],
    'model' => [
        'namespace_pattern' => 'AlingAi\\Models',
        'template' => <<<'EOT'
<?php

declare(strict_types=1);

namespace {{NAMESPACE}};

use AlingAi\Models\BaseModel;

/**
 * {{CLASS_NAME}}
 * 
 * {{CLASS_DESCRIPTION}}
 */
class {{CLASS_NAME}} extends BaseModel
{
    /**
     * 表名
     */
    protected string $table = '{{TABLE_NAME}}';
    
    /**
     * 主键
     */
    protected string $primaryKey = 'id';
    
    /**
     * 可填充字段
     */
    protected array $fillable = [
        'name',
        'description',
        'created_at',
        'updated_at'
    ];
    
    /**
     * 构造函数
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
    
    /**
     * 获取所有记录
     */
    public function getAll(): array
    {
        return $this->all();
    }
    
    /**
     * 根据ID查找记录
     */
    public function findById(int $id): ?array
    {
        return $this->find($id);
    }
}
EOT
    ],
    'middleware' => [
        'namespace_pattern' => 'AlingAi\\Middleware',
        'template' => <<<'EOT'
<?php

declare(strict_types=1);

namespace {{NAMESPACE}};

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;

/**
 * {{CLASS_NAME}}
 * 
 * {{CLASS_DESCRIPTION}}
 */
class {{CLASS_NAME}} implements MiddlewareInterface
{
    private LoggerInterface $logger;
    
    /**
     * 构造函数
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * 处理请求
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $this->logger->info('{{CLASS_NAME}} 处理请求');
        
        // 在这里添加中间件逻辑
        
        return $handler->handle($request);
    }
}
EOT
    ],
    'default' => [
        'namespace_pattern' => 'AlingAi',
        'template' => <<<'EOT'
<?php

declare(strict_types=1);

namespace {{NAMESPACE}};

/**
 * {{CLASS_NAME}}
 * 
 * {{CLASS_DESCRIPTION}}
 */
class {{CLASS_NAME}}
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        // 初始化代码
    }
    
    /**
     * 初始化
     */
    public function initialize(): bool
    {
        return true;
    }
    
    /**
     * 执行
     */
    public function execute(): array
    {
        return [
            'status' => 'success'
        ];
    }
}
EOT
    ]
];

// 递归扫描目录
function scanDirectory($dir, &$stats, $templates)
{
    $items = scandir($dir);
    $stats['directories_processed']++;
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $path = $dir . '/' . $item;
        
        if (is_dir($path)) {
            scanDirectory($path, $stats, $templates);
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            enhanceFile($path, $stats, $templates);
        }
    }
}

// 增强文件内容
function enhanceFile($filePath, &$stats, $templates)
{
    $stats['files_scanned']++;
    
    $content = file_get_contents($filePath);
    $fileSize = filesize($filePath);
    
    // 检查文件是否为空或只有基本结构
    if ($fileSize === 0 || preg_match('/class\s+\w+\s*{[^}]*}\s*$/s', $content)) {
        try {
            $enhancedContent = generateEnhancedContent($filePath, $content, $templates);
            file_put_contents($filePath, $enhancedContent);
            $stats['files_enhanced']++;
            echo "增强文件: " . $filePath . PHP_EOL;
        } catch (\Exception $e) {
            $stats['errors']++;
            echo "错误: " . $e->getMessage() . " - " . $filePath . PHP_EOL;
        }
    }
}

// 生成增强的文件内容
function generateEnhancedContent($filePath, $originalContent, $templates)
{
    // 提取类名
    preg_match('/class\s+(\w+)/', $originalContent, $classMatches);
    $className = $classMatches[1] ?? pathinfo($filePath, PATHINFO_FILENAME);
    
    // 提取或生成命名空间
    preg_match('/namespace\s+([\w\\\\]+)/', $originalContent, $nsMatches);
    $namespace = $nsMatches[1] ?? generateNamespace($filePath);
    
    // 确定模板类型
    $templateType = determineTemplateType($filePath, $className, $namespace);
    $template = $templates[$templateType];
    
    // 生成类描述
    $classDescription = generateClassDescription($className, $templateType);
    
    // 替换模板变量
    $content = str_replace(
        ['{{NAMESPACE}}', '{{CLASS_NAME}}', '{{CLASS_DESCRIPTION}}', '{{TABLE_NAME}}'],
        [$namespace, $className, $classDescription, strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $className))],
        $template['template']
    );
    
    return $content;
}

// 根据文件路径生成命名空间
function generateNamespace($filePath)
{
    $relativePath = str_replace([__DIR__ . '/src/', '/'], ['', '\\'], dirname($filePath));
    return 'AlingAi\\' . $relativePath;
}

// 确定模板类型
function determineTemplateType($filePath, $className, $namespace)
{
    if (strpos($filePath, '/Controllers/') !== false || strpos($className, 'Controller') !== false) {
        return 'controller';
    } elseif (strpos($filePath, '/Services/') !== false || strpos($className, 'Service') !== false) {
        return 'service';
    } elseif (strpos($filePath, '/Models/') !== false || strpos($className, 'Model') !== false) {
        return 'model';
    } elseif (strpos($filePath, '/Middleware/') !== false || strpos($className, 'Middleware') !== false) {
        return 'middleware';
    } else {
        return 'default';
    }
}

// 生成类描述
function generateClassDescription($className, $type)
{
    switch ($type) {
        case 'controller':
            return '处理' . str_replace('Controller', '', $className) . '相关的HTTP请求';
        case 'service':
            return '提供' . str_replace('Service', '', $className) . '相关的业务逻辑服务';
        case 'model':
            return '表示' . $className . '数据模型';
        case 'middleware':
            return '提供' . str_replace('Middleware', '', $className) . '中间件功能';
        default:
            return '提供' . $className . '相关功能';
    }
}

// 修复helpers.php文件
function fixHelpersFile()
{
    $helpersPath = __DIR__ . '/src/helpers.php';
    $content = <<<'EOT'
<?php

/**
 * AlingAi Pro 全局辅助函数
 */

if (!function_exists('app')) {
    /**
     * 获取应用实例
     *
     * @return \AlingAi\Core\Application
     */
    function app()
    {
        global $app;
        return $app;
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
        
        if ($config === null) {
            $configPath = __DIR__ . '/../config/config.php';
            $config = file_exists($configPath) ? require $configPath : [];
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

if (!function_exists('base_path')) {
    /**
     * 获取基础路径
     *
     * @param string $path 相对路径
     * @return string
     */
    function base_path($path = '')
    {
        return __DIR__ . '/../' . $path;
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
        return __DIR__ . '/../storage/' . $path;
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
        return __DIR__ . '/../public/' . $path;
    }
}

if (!function_exists('json_response')) {
    /**
     * 创建JSON响应
     *
     * @param mixed $data 数据
     * @param int $status HTTP状态码
     * @return \Psr\Http\Message\ResponseInterface
     */
    function json_response($data, $status = 200)
    {
        $response = app()->getContainer()->get('response');
        $response->getBody()->write(json_encode($data));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
EOT;
    
    file_put_contents($helpersPath, $content);
    echo "修复helpers.php文件" . PHP_EOL;
}

// 开始执行
echo "开始完善文件结构..." . PHP_EOL;
$startTime = microtime(true);

// 修复helpers.php
fixHelpersFile();

// 扫描目录
scanDirectory($srcDir, $stats, $templates);

$endTime = microtime(true);
$executionTime = round($endTime - $startTime, 2);

echo "完成！" . PHP_EOL;
echo "统计信息：" . PHP_EOL;
echo "- 扫描文件数: " . $stats['files_scanned'] . PHP_EOL;
echo "- 增强文件数: " . $stats['files_enhanced'] . PHP_EOL;
echo "- 处理目录数: " . $stats['directories_processed'] . PHP_EOL;
echo "- 错误数: " . $stats['errors'] . PHP_EOL;
echo "- 执行时间: " . $executionTime . " 秒" . PHP_EOL;