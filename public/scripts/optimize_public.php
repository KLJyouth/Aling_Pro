<?php

class PublicOptimizer {
    private $rootDir;
    private $logFile;
    
    public function __construct($rootDir) {
        $this->rootDir = rtrim($rootDir, '\\/'];
        $this->logFile = 'logs/public_optimizer.log';
        
        // 确保日志目录存在
        if (!is_dir('logs')) {
            mkdir('logs', 0755, true];
        }
    }
    
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s'];
        $logMessage = "[$timestamp] $message\n";
        file_put_contents($this->logFile, $logMessage, FILE_APPEND];
    }
    
    public function optimize() {
        try {
            // 1. 整理文件
            $this->organizeFiles(];
            
            // 2. 优化HTML文件
            $this->optimizeHtmlFiles(];
            
            // 3. 优化静态资�?
            $this->optimizeStaticResources(];
            
            $this->log('Public目录优化完成'];
        } catch (\Exception $e) {
            $this->log('错误: ' . $e->getMessage()];
            throw $e;
        }
    }
    
    private function organizeFiles() {
        // 创建必要的目�?
        $dirs = [
            'tests',
            'backups',
            'temp',
            'optimized'
        ];
        
        foreach ($dirs as $dir) {
            $path = $this->rootDir . '\\' . $dir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true];
            }
        }
        
        // 移动测试文件
        $testFiles = glob($this->rootDir . '\\test*.php'];
        foreach ($testFiles as $file) {
            $newPath = $this->rootDir . '\\tests\\' . basename($file];
            rename($file, $newPath];
            $this->log('移动测试文件: ' . $file . ' -> ' . $newPath];
        }
        
        // 移动备份文件
        $backupFiles = glob($this->rootDir . '\\*-backup*'];
        foreach ($backupFiles as $file) {
            $newPath = $this->rootDir . '\\backups\\' . basename($file];
            rename($file, $newPath];
            $this->log('移动备份文件: ' . $file . ' -> ' . $newPath];
        }
    }
    
    private function optimizeHtmlFiles() {
        $htmlFiles = glob($this->rootDir . '\\*.html'];
        foreach ($htmlFiles as $file) {
            $this->optimizeHtmlFile($file];
        }
    }
    
    private function optimizeHtmlFile($file) {
        $content = file_get_contents($file];
        
        // 1. 移除注释
        $content = preg_replace('/<!--(?!<!)[^\[>][\s\S]*?-->/', '', $content];
        
        // 2. 移除空白
        $content = preg_replace('/\s+/', ' ', $content];
        
        // 3. 优化HTML结构
        $content = $this->optimizeHtmlStructure($content];
        
        // 4. 添加响应式设�?
        $content = $this->addResponsiveDesign($content];
        
        // 5. 优化资源加载
        $content = $this->optimizeResourceLoading($content];
        
        // 保存优化后的文件
        $optimizedPath = $this->rootDir . '\\optimized\\' . basename($file];
        file_put_contents($optimizedPath, $content];
        
        $this->log('优化HTML文件: ' . $file];
    }
    
    private function optimizeHtmlStructure($content) {
        // 1. 确保DOCTYPE声明
        if (!preg_match('/<!DOCTYPE/i', $content)) {
            $content = '<!DOCTYPE html>' . $content;
        }
        
        // 2. 确保meta charset
        if (!preg_match('/<meta[^>]*charset/i', $content)) {
            $content = str_replace('<head>', '<head><meta charset="UTF-8">', $content];
        }
        
        // 3. 添加viewport meta
        if (!preg_match('/<meta[^>]*viewport/i', $content)) {
            $content = str_replace('<head>', '<head><meta name="viewport" content="width=device-width, initial-scale=1.0">', $content];
        }
        
        return $content;
    }
    
    private function addResponsiveDesign($content) {
        // 添加响应式CSS
        $responsiveCss = '
        <style>
            @media (max-width: 768px) {
                .container { width: 100%; padding: 0 15px; }
                .row { flex-direction: column; }
                .col { width: 100%; }
            }
            @media (max-width: 480px) {
                body { font-size: 14px; }
                h1 { font-size: 24px; }
                h2 { font-size: 20px; }
            }
        </style>';
        
        return str_replace('</head>', $responsiveCss . '</head>', $content];
    }
    
    private function optimizeResourceLoading($content) {
        // 1. 延迟加载图片
        $content = preg_replace('/<img(.*?)>/i', '<img$1 loading="lazy">', $content];
        
        // 2. 异步加载JavaScript
        $content = preg_replace('/<script(?!.*async)(?!.*defer)(.*?)>/i', '<script$1 async>', $content];
        
        // 3. 预加载关键资�?
        $content = str_replace('<head>', '<head> <link rel="preload" href="/assets/css/main.css" as="style"> <link rel="preload" href="/assets/js/main.js" as="script">', $content];
        
        return $content;
    }
    
    private function optimizeStaticResources() {
        // 1. 压缩CSS
        $cssFiles = glob($this->rootDir . '\\assets\\css\\*.css'];
        foreach ($cssFiles as $file) {
            $this->minifyCss($file];
        }
        
        // 2. 压缩JavaScript
        $jsFiles = glob($this->rootDir . '\\assets\\js\\*.js'];
        foreach ($jsFiles as $file) {
            $this->minifyJs($file];
        }
        
        // 3. 优化图片（已注释，因GD库未启用�?
        // $imageFiles = glob($this->rootDir . '\\assets\\images\\*.{jpg,jpeg,png,gif}', GLOB_BRACE];
        // foreach ($imageFiles as $file) {
        //     $this->optimizeImage($file];
        // }
    }
    
    private function minifyCss($file) {
        $content = file_get_contents($file];
        // 移除注释
        $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content];
        // 移除空白
        $content = preg_replace('/\s+/', ' ', $content];
        // 保存压缩后的文件
        file_put_contents($file, $content];
        $this->log('压缩CSS文件: ' . $file];
    }
    
    private function minifyJs($file) {
        $content = file_get_contents($file];
        // 移除注释
        $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content];
        // 移除空白
        $content = preg_replace('/\s+/', ' ', $content];
        // 保存压缩后的文件
        file_put_contents($file, $content];
        $this->log('压缩JavaScript文件: ' . $file];
    }
    
    // private function optimizeImage($file) {
    //     // 使用GD库优化图�?
    //     $image = imagecreatefromstring(file_get_contents($file)];
    //     if ($image) {
    //         $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION)];
    //         $quality = 85; // 压缩质量
    //         switch ($ext) {
    //             case 'jpg':
    //             case 'jpeg':
    //                 imagejpeg($image, $file, $quality];
    //                 break;
    //             case 'png':
    //                 imagepng($image, $file, 9];
    //                 break;
    //             case 'gif':
    //                 imagegif($image, $file];
    //                 break;
    //         }
    //         imagedestroy($image];
    //         $this->log('优化图片: ' . $file];
    //     }
    // }
}

// 执行优化
$optimizer = new PublicOptimizer(__DIR__ . '\\..\\public'];
$optimizer->optimize(]; 
