<?php

class PublicFileChecker {
    private $rootDir;
    private $logFile;
    private $backupDir;
    private $excludedDirs = ['backups', 'temp', 'optimized'];
    
    public function __construct($rootDir = 'public') {
        $this->rootDir = $rootDir;
        $this->logFile = 'logs/public_checker.log';
        $this->backupDir = 'public/backups';
        
        if (!file_exists('logs')) {
            mkdir('logs', 0777, true);
        }
    }
    
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($this->logFile, "[$timestamp] $message\n", FILE_APPEND);
    }
    
    public function check() {
        try {
            $this->log("开始检查目录: {$this->rootDir}");
            $this->scanDirectory($this->rootDir);
            $this->log("检查完成");
        } catch (Exception $e) {
            $this->log("错误: " . $e->getMessage());
        }
    }
    
    private function scanDirectory($dir) {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $path = $dir . '/' . $file;
            
            // 跳过排除的目录
            if (is_dir($path) && !in_array($file, $this->excludedDirs)) {
                $this->scanDirectory($path);
                continue;
            }
            
            // 检查文件
            if (is_file($path)) {
                $this->checkFile($path);
            }
        }
    }
    
    private function checkFile($file) {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        
        switch ($extension) {
            case 'php':
                $this->checkPhpFile($file);
                break;
            case 'html':
            case 'htm':
                $this->checkHtmlFile($file);
                break;
            case 'js':
                $this->checkJsFile($file);
                break;
            case 'css':
                $this->checkCssFile($file);
                break;
        }
    }
    
    private function checkPhpFile($file) {
        $content = file_get_contents($file);
        
        // 检查基本语法
        if (php_check_syntax($file, $error)) {
            $this->log("PHP语法错误 [$file]: $error");
            return;
        }
        
        // 检查常见问题
        $issues = [];
        
        // 检查未使用的变量
        if (strpos($content, '$') !== false) {
            $issues[] = "可能存在未使用的变量";
        }
        
        // 检查SQL注入风险
        if (strpos($content, '$_GET') !== false || 
            strpos($content, '$_POST') !== false || 
            strpos($content, '$_REQUEST') !== false) {
            $issues[] = "可能存在SQL注入风险";
        }
        
        // 检查XSS风险
        if (strpos($content, 'echo $_') !== false) {
            $issues[] = "可能存在XSS风险";
        }
        
        if (!empty($issues)) {
            $this->log("发现问题 [$file]: " . implode(", ", $issues));
        }
    }
    
    private function checkHtmlFile($file) {
        $content = file_get_contents($file);
        
        // 检查常见问题
        $issues = [];
        
        // 检查缺少DOCTYPE
        if (strpos($content, '<!DOCTYPE') === false) {
            $issues[] = "缺少DOCTYPE声明";
        }
        
        // 检查缺少字符集声明
        if (strpos($content, 'charset') === false) {
            $issues[] = "缺少字符集声明";
        }
        
        // 检查图片缺少alt属性
        if (strpos($content, '<img') !== false && strpos($content, 'alt=') === false) {
            $issues[] = "存在缺少alt属性的图片";
        }
        
        if (!empty($issues)) {
            $this->log("发现问题 [$file]: " . implode(", ", $issues));
        }
    }
    
    private function checkJsFile($file) {
        $content = file_get_contents($file);
        
        // 检查常见问题
        $issues = [];
        
        // 检查console.log
        if (strpos($content, 'console.log') !== false) {
            $issues[] = "存在console.log语句";
        }
        
        // 检查eval使用
        if (strpos($content, 'eval(') !== false) {
            $issues[] = "使用了eval函数";
        }
        
        if (!empty($issues)) {
            $this->log("发现问题 [$file]: " . implode(", ", $issues));
        }
    }
    
    private function checkCssFile($file) {
        $content = file_get_contents($file);
        
        // 检查常见问题
        $issues = [];
        
        // 检查!important使用
        if (strpos($content, '!important') !== false) {
            $issues[] = "使用了!important";
        }
        
        // 检查内联样式
        if (strpos($content, 'style=') !== false) {
            $issues[] = "存在内联样式";
        }
        
        if (!empty($issues)) {
            $this->log("发现问题 [$file]: " . implode(", ", $issues));
        }
    }
}

// 运行检查
$checker = new PublicFileChecker();
$checker->check();
