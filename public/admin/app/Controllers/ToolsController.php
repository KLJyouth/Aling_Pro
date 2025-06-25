<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * 维护工具控制�?
 * 负责处理维护工具相关请求
 */
class ToolsController extends Controller
{
    /**
     * 工具目录路径
     * @var string
     */
    protected $toolsPath;
    
    /**
     * 构造函�?
     */
    public function __construct()
    {
        $this->toolsPath = BASE_PATH . '/tools';
    }
    
    /**
     * 显示工具列表页面
     * @return void
     */
    public function index()
    {
        // 获取所有工�?
        $tools = $this->getAllTools(];
        
        // 按类别分�?
        $toolsByCategory = $this->groupToolsByCategory($tools];
        
        // 渲染视图
        $this->view('tools.index', [
            'tools' => $tools,
            'toolsByCategory' => $toolsByCategory,
            'pageTitle' => 'IT运维中心 - 维护工具'
        ]];
    }
    
    /**
     * 显示PHP修复工具页面
     * @return void
     */
    public function phpFix()
    {
        // 获取PHP修复工具
        $phpFixTools = $this->getToolsByType('fix_php'];
        
        // 渲染视图
        $this->view('tools.php-fix', [
            'phpFixTools' => $phpFixTools,
            'pageTitle' => 'IT运维中心 - PHP修复工具'
        ]];
    }
    
    /**
     * 运行修复工具
     * @return void
     */
    public function runFix()
    {
        // 获取请求参数
        $toolName = $this->input('tool_name'];
        $params = $this->input('params', ''];
        
        // 验证参数
        $errors = $this->validate([
            'tool_name' => 'required'
        ]];
        
        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'errors' => $errors
            ],  400];
            return;
        }
        
        // 检查工具是否存�?
        $toolPath = $this->toolsPath . '/' . $toolName;
        if (!file_exists($toolPath)) {
            $this->json([
                'success' => false,
                'errors' => ['tool_name' => ['工具不存�?]]
            ],  404];
            return;
        }
        
        // 执行工具
        $output = $this->executePhpTool($toolName, $params];
        
        // 记录日志
        $this->logToolExecution($toolName, $params, $output];
        
        // 返回结果
        $this->json([
            'success' => true,
            'output' => $output
        ]];
    }
    
    /**
     * 显示命名空间检查页�?
     * @return void
     */
    public function namespaceCheck()
    {
        // 获取命名空间检查工�?
        $namespaceTools = $this->getToolsByType('namespace'];
        
        // 渲染视图
        $this->view('tools.namespace-check', [
            'namespaceTools' => $namespaceTools,
            'pageTitle' => 'IT运维中心 - 命名空间检�?
        ]];
    }
    
    /**
     * 显示编码修复页面
     * @return void
     */
    public function encodingFix()
    {
        // 获取编码修复工具
        $encodingTools = $this->getToolsByType('encoding'];
        
        // 渲染视图
        $this->view('tools.encoding-fix', [
            'encodingTools' => $encodingTools,
            'pageTitle' => 'IT运维中心 - 编码修复'
        ]];
    }
    
    /**
     * 获取所有工�?
     * @return array 工具列表
     */
    private function getAllTools()
    {
        $tools = [];
        
        // 检查工具目录是否存�?
        if (!is_dir($this->toolsPath)) {
            return $tools;
        }
        
        // 获取所有PHP工具文件
        $toolFiles = glob($this->toolsPath . '/*.php'];
        
        foreach ($toolFiles as $toolFile) {
            $fileName = basename($toolFile];
            $toolName = basename($toolFile, '.php'];
            
            // 获取工具信息
            $info = $this->getToolInfo($toolFile];
            
            $tools[] = [
                'name' => $toolName,
                'file' => $fileName,
                'description' => $info['description'] ?? '无描�?,
                'category' => $info['category'] ?? $this->guessToolCategory($toolName],
                'lastModified' => date('Y-m-d H:i:s', filemtime($toolFile)],
                'size' => filesize($toolFile)
            ];
        }
        
        return $tools;
    }
    
    /**
     * 按类别分组工�?
     * @param array $tools 工具列表
     * @return array 分组后的工具
     */
    private function groupToolsByCategory($tools)
    {
        $grouped = [];
        
        foreach ($tools as $tool) {
            $category = $tool['category'];
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $tool;
        }
        
        return $grouped;
    }
    
    /**
     * 根据类型获取工具
     * @param string $type 工具类型
     * @return array 工具列表
     */
    private function getToolsByType($type)
    {
        $tools = $this->getAllTools(];
        
        return array_filter($tools, function($tool) use ($type) {
            return strpos($tool['name'],  $type) !== false || 
                   strpos($tool['category'],  $type) !== false;
        }];
    }
    
    /**
     * 获取工具信息
     * @param string $toolFile 工具文件路径
     * @return array 工具信息
     */
    private function getToolInfo($toolFile)
    {
        $info = [
            'description' => '',
            'category' => ''
        ];
        
        // 读取文件前几�?
        $file = fopen($toolFile, 'r'];
        if ($file) {
            $lineCount = 0;
            $inDocBlock = false;
            
            while (($line = fgets($file)) !== false && $lineCount < 20) {
                $lineCount++;
                
                // 检查是否为文档块开�?
                if (strpos($line, '/**') !== false) {
                    $inDocBlock = true;
                    continue;
                }
                
                // 检查是否为文档块结�?
                if (strpos($line, '*/') !== false) {
                    $inDocBlock = false;
                    continue;
                }
                
                // 在文档块内寻找描述和类别
                if ($inDocBlock) {
                    $line = trim($line];
                    $line = trim($line, '* '];
                    
                    if (strpos($line, '@description') !== false) {
                        $info['description'] = trim(str_replace('@description', '', $line)];
                    }
                    
                    if (strpos($line, '@category') !== false) {
                        $info['category'] = trim(str_replace('@category', '', $line)];
                    }
                }
                
                // 如果不在文档块内，检查是否有注释
                if (!$inDocBlock && empty($info['description'])) {
                    if (strpos($line, '//') !== false) {
                        $comment = trim(substr($line, strpos($line, '//') + 2)];
                        if (!empty($comment) && empty($info['description'])) {
                            $info['description'] = $comment;
                        }
                    }
                }
            }
            
            fclose($file];
        }
        
        return $info;
    }
    
    /**
     * 根据工具名称猜测类别
     * @param string $toolName 工具名称
     * @return string 工具类别
     */
    private function guessToolCategory($toolName)
    {
        $toolName = strtolower($toolName];
        
        if (strpos($toolName, 'fix_') === 0) {
            return 'fix';
        }
        
        if (strpos($toolName, 'check_') === 0) {
            return 'check';
        }
        
        if (strpos($toolName, 'validate_') === 0) {
            return 'validate';
        }
        
        if (strpos($toolName, 'namespace') !== false) {
            return 'namespace';
        }
        
        if (strpos($toolName, 'encoding') !== false || strpos($toolName, 'bom') !== false) {
            return 'encoding';
        }
        
        if (strpos($toolName, 'php') !== false) {
            return 'php';
        }
        
        return 'other';
    }
    
    /**
     * 执行PHP工具
     * @param string $toolName 工具名称
     * @param string $params 参数
     * @return string 执行结果
     */
    private function executePhpTool($toolName, $params)
    {
        $toolPath = $this->toolsPath . '/' . $toolName;
        
        // 检查工具是否存�?
        if (!file_exists($toolPath)) {
            return "错误：工�?{$toolName} 不存�?;
        }
        
        // 执行命令
        $command = "php \"{$toolPath}\" {$params} 2>&1";
        $output = [];
        $returnVar = 0;
        
        exec($command, $output, $returnVar];
        
        return implode("\n", $output) . ($returnVar !== 0 ? "\n执行返回代码: {$returnVar}" : ''];
    }
    
    /**
     * 记录工具执行日志
     * @param string $toolName 工具名称
     * @param string $params 参数
     * @param string $output 输出结果
     * @return void
     */
    private function logToolExecution($toolName, $params, $output)
    {
        $logsDir = BASE_PATH . '/storage/logs';
        
        // 确保日志目录存在
        if (!is_dir($logsDir)) {
            mkdir($logsDir, 0755, true];
        }
        
        $logFile = $logsDir . '/tools_execution.log';
        
        // 准备日志内容
        $timestamp = date('Y-m-d H:i:s'];
        $logContent = "[{$timestamp}] 执行工具: {$toolName}\n";
        $logContent .= "参数: {$params}\n";
        $logContent .= "输出:\n{$output}\n";
        $logContent .= "----------------------------------------\n";
        
        // 写入日志
        file_put_contents($logFile, $logContent, FILE_APPEND];
    }
} 
