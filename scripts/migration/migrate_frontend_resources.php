<?php
/**
 * 前端资源迁移脚本
 * 将AlingAi原项目的前端资源完整迁移到AlingAi_pro
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

class FrontendResourceMigrator {
    
    private $sourceDir;
    private $targetDir;
    private $migratedFiles = [];
    private $errors = [];
    
    public function __construct() {
        $this->sourceDir = dirname(__DIR__) . '/public';
        $this->targetDir = __DIR__ . '/public';
        
        // 确保目标目录存在
        if (!is_dir($this->targetDir)) {
            mkdir($this->targetDir, 0755, true);
        }
    }
    
    /**
     * 开始迁移
     */
    public function migrate() {
        echo "🚀 开始前端资源迁移...\n";
        echo "源目录: {$this->sourceDir}\n";
        echo "目标目录: {$this->targetDir}\n\n";
        
        // 1. 迁移HTML文件
        $this->migrateHTMLFiles();
        
        // 2. 迁移JavaScript文件  
        $this->migrateJavaScriptFiles();
        
        // 3. 迁移CSS文件
        $this->migrateCSSFiles();
        
        // 4. 迁移图片和资源文件
        $this->migrateAssetFiles();
        
        // 5. 更新路径引用
        $this->updatePaths();
        
        // 6. 生成报告
        $this->generateReport();
    }
    
    /**
     * 迁移HTML文件
     */
    private function migrateHTMLFiles() {
        echo "📄 迁移HTML文件...\n";
        
        $htmlFiles = [
            'index.html',
            'chat.html',
            'login.html',
            'register.html', 
            'dashboard.html',
            'admin.html',
            'profile.html',
            'contact.html',
            'privacy.html',
            'terms.html'
        ];
        
        foreach ($htmlFiles as $file) {
            $sourcePath = $this->sourceDir . '/' . $file;
            $targetPath = $this->targetDir . '/' . $file;
            
            if (file_exists($sourcePath)) {
                $content = file_get_contents($sourcePath);
                
                // 更新路径引用 - 将原始路径转换为新的asset路径
                $content = $this->updateHTMLPaths($content);
                
                if (file_put_contents($targetPath, $content) !== false) {
                    $this->migratedFiles[] = $file;
                    echo "✅ 迁移成功: $file\n";
                } else {
                    $this->errors[] = "迁移失败: $file";
                    echo "❌ 迁移失败: $file\n";
                }
            } else {
                echo "⚠️ 文件不存在: $file\n";
            }
        }
    }
    
    /**
     * 迁移JavaScript文件
     */
    private function migrateJavaScriptFiles() {
        echo "\n📜 迁移JavaScript文件...\n";
        
        $jsSourceDir = $this->sourceDir . '/js';
        $jsTargetDir = $this->targetDir . '/assets/js';
        
        if (!is_dir($jsTargetDir)) {
            mkdir($jsTargetDir, 0755, true);
        }
        
        $this->copyDirectoryRecursive($jsSourceDir, $jsTargetDir);
        echo "✅ JavaScript文件迁移完成\n";
    }
    
    /**
     * 迁移CSS文件
     */
    private function migrateCSSFiles() {
        echo "\n🎨 迁移CSS文件...\n";
        
        $cssSourceDir = $this->sourceDir . '/css';
        $cssTargetDir = $this->targetDir . '/assets/css';
        
        if (!is_dir($cssTargetDir)) {
            mkdir($cssTargetDir, 0755, true);
        }
        
        $this->copyDirectoryRecursive($cssSourceDir, $cssTargetDir);
        echo "✅ CSS文件迁移完成\n";
    }
    
    /**
     * 迁移图片和其他资源文件
     */
    private function migrateAssetFiles() {
        echo "\n🖼️ 迁移图片和资源文件...\n";
        
        $assetDirs = ['images', 'assets', 'docs', 'vido'];
        $assetTargetDir = $this->targetDir . '/assets';
        
        if (!is_dir($assetTargetDir)) {
            mkdir($assetTargetDir, 0755, true);
        }
        
        foreach ($assetDirs as $dir) {
            $sourceAssetDir = $this->sourceDir . '/' . $dir;
            $targetAssetDir = $assetTargetDir . '/' . $dir;
            
            if (is_dir($sourceAssetDir)) {
                $this->copyDirectoryRecursive($sourceAssetDir, $targetAssetDir);
                echo "✅ 迁移目录: $dir\n";
            }
        }
        
        // 迁移根目录的图片文件
        $imageFiles = glob($this->sourceDir . '/*.{jpg,jpeg,png,gif,svg,ico}', GLOB_BRACE);
        foreach ($imageFiles as $imagePath) {
            $filename = basename($imagePath);
            $targetPath = $assetTargetDir . '/images/' . $filename;
            
            if (!is_dir(dirname($targetPath))) {
                mkdir(dirname($targetPath), 0755, true);
            }
            
            if (copy($imagePath, $targetPath)) {
                echo "✅ 迁移图片: $filename\n";
            }
        }
        
        echo "✅ 资源文件迁移完成\n";
    }
    
    /**
     * 递归复制目录
     */
    private function copyDirectoryRecursive($source, $target) {
        if (!is_dir($source)) {
            return;
        }
        
        if (!is_dir($target)) {
            mkdir($target, 0755, true);
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $targetPath = $target . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            
            if ($item->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                copy($item, $targetPath);
            }
        }
    }
    
    /**
     * 更新HTML文件中的路径引用
     */
    private function updateHTMLPaths($content) {
        // 更新CSS路径
        $content = preg_replace('/href=["\']css\/([^"\']+)["\']/', 'href="assets/css/$1"', $content);
        
        // 更新JS路径
        $content = preg_replace('/src=["\']js\/([^"\']+)["\']/', 'src="assets/js/$1"', $content);
        
        // 更新图片路径
        $content = preg_replace('/src=["\']images\/([^"\']+)["\']/', 'src="assets/images/$1"', $content);
        $content = preg_replace('/src=["\']([^"\']*\.(jpg|jpeg|png|gif|svg|ico))["\']/', 'src="assets/images/$1"', $content);
        
        // 更新其他资源路径
        $content = preg_replace('/href=["\']([^"\']*\.(jpg|jpeg|png|gif|svg|ico))["\']/', 'href="assets/images/$1"', $content);
        
        return $content;
    }
    
    /**
     * 更新所有文件中的路径引用
     */
    private function updatePaths() {
        echo "\n🔄 更新路径引用...\n";
        
        // 更新HTML文件
        $htmlFiles = glob($this->targetDir . '/*.html');
        foreach ($htmlFiles as $file) {
            $content = file_get_contents($file);
            $updatedContent = $this->updateHTMLPaths($content);
            file_put_contents($file, $updatedContent);
        }
        
        // 更新CSS文件中的路径
        $cssFiles = glob($this->targetDir . '/assets/css/*.css');
        foreach ($cssFiles as $file) {
            $content = file_get_contents($file);
            // 更新CSS中的背景图片路径等
            $content = preg_replace('/url\(["\']?\.\.\/images\/([^"\')\s]+)["\']?\)/', 'url("../images/$1")', $content);
            file_put_contents($file, $content);
        }
        
        echo "✅ 路径更新完成\n";
    }
    
    /**
     * 生成迁移报告
     */
    private function generateReport() {
        echo "\n📊 迁移报告\n";
        echo "===================\n";
        echo "迁移成功文件数: " . count($this->migratedFiles) . "\n";
        echo "错误数: " . count($this->errors) . "\n";
        
        if (!empty($this->errors)) {
            echo "\n❌ 错误列表:\n";
            foreach ($this->errors as $error) {
                echo "  - $error\n";
            }
        }
        
        echo "\n✅ 前端资源迁移完成！\n";
        
        // 创建.htaccess文件用于URL重写
        $this->createHTAccessFile();
        
        // 创建nginx配置文件更新建议
        $this->createNginxConfigUpdate();
    }
    
    /**
     * 创建.htaccess文件
     */
    private function createHTAccessFile() {
        $htaccessContent = <<<'HTACCESS'
# AlingAi Pro .htaccess Configuration
# Generated by Frontend Resource Migrator

# Enable URL Rewriting
RewriteEngine On

# Redirect legacy asset paths
RewriteRule ^css/(.*)$ assets/css/$1 [R=301,L]
RewriteRule ^js/(.*)$ assets/js/$1 [R=301,L]
RewriteRule ^images/(.*)$ assets/images/$1 [R=301,L]

# PHP Routes
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' https:; connect-src 'self' wss: https:;"
</IfModule>

# Cache Control
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
    ExpiresByType image/ico "access plus 1 year"
    ExpiresByType image/x-icon "access plus 1 year"
</IfModule>

# Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css text/javascript application/javascript text/xml application/xml application/xml+rss text/plain
</IfModule>
HTACCESS;

        file_put_contents($this->targetDir . '/.htaccess', $htaccessContent);
        echo "✅ 创建.htaccess文件\n";
    }
    
    /**
     * 创建nginx配置更新建议
     */
    private function createNginxConfigUpdate() {
        $nginxConfig = <<<'NGINX'
# Nginx Configuration Update for AlingAi Pro
# Add these rules to your nginx configuration

# Legacy asset redirects
location ~* ^/(css|js|images)/(.*)$ {
    return 301 /assets/$1/$2;
}

# PHP handling
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_param PATH_INFO $fastcgi_path_info;
}

# Static asset caching
location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
    add_header X-Content-Type-Options nosniff;
}

# Security headers
add_header X-Frame-Options DENY always;
add_header X-XSS-Protection "1; mode=block" always;
add_header X-Content-Type-Options nosniff always;
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' https:; connect-src 'self' wss: https:;" always;
NGINX;

        file_put_contents(__DIR__ . '/nginx/nginx-config-update.conf', $nginxConfig);
        echo "✅ 创建Nginx配置更新文件\n";
    }
}

// 执行迁移
try {
    $migrator = new FrontendResourceMigrator();
    $migrator->migrate();
} catch (Exception $e) {
    echo "❌ 迁移过程中发生错误: " . $e->getMessage() . "\n";
    exit(1);
}
?>
