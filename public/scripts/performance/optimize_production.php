<?php

/**
 * 生产环境配置优化脚本
 * 自动应用生产环境最佳实践配�?
 */

class ProductionOptimizer 
{
    private $configPath;
    private $backupPath;
    
    public function __construct() 
    {
        $this->configPath = __DIR__ . '/../config/production.ini';
        $this->backupPath = __DIR__ . '/../config/backup/';
    }
    
    /**
     * 应用生产环境配置
     */
    public function applyProductionConfig(): void 
    {
        echo "🔧 应用生产环境配置...\n";
        
        // 确保备份目录存在
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true];
        }
        
        // 备份当前配置
        $this->backupCurrentConfig(];
        
        // 应用PHP配置
        $this->applyPhpConfig(];
        
        // 设置环境变量
        $this->setEnvironmentVariables(];
        
        // 优化.htaccess
        $this->optimizeHtaccess(];
        
        echo "�?生产环境配置应用完成\n";
    }
    
    /**
     * 备份当前配置
     */
    private function backupCurrentConfig(): void 
    {
        echo "📦 备份当前配置...\n";
        
        $timestamp = date('Y-m-d_H-i-s'];
        $backupFile = $this->backupPath . "config_backup_{$timestamp}.ini";
        
        if (file_exists($this->configPath)) {
            copy($this->configPath, $backupFile];
            echo "�?配置已备份到: {$backupFile}\n";
        }
    }
    
    /**
     * 应用PHP配置
     */
    private function applyPhpConfig(): void 
    {
        echo "⚙️ 应用PHP生产配置...\n";
        
        // 关闭错误显示
        ini_set('display_errors', '0'];
        ini_set('display_startup_errors', '0'];
        
        // 启用错误日志
        ini_set('log_errors', '1'];
        
        // 设置错误报告级别
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT];
        
        // 安全设置
        ini_set('expose_php', '0'];
        ini_set('allow_url_fopen', '0'];
        ini_set('allow_url_include', '0'];
        
        // Session安全
        ini_set('session.cookie_httponly', '1'];
        ini_set('session.cookie_secure', '1'];
        ini_set('session.use_strict_mode', '1'];
        
        echo "�?PHP配置已优化\n";
    }
    
    /**
     * 设置环境变量
     */
    private function setEnvironmentVariables(): void 
    {
        echo "🌐 设置生产环境变量...\n";
        
        // 检�?env文件
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile];
            
            // 确保生产环境设置
            $productionSettings = [
                'APP_ENV=production',
                'APP_DEBUG=false',
                'LOG_LEVEL=error',
                'CACHE_ENABLED=true',
                'SESSION_SECURE=true'
            ];
            
            foreach ($productionSettings as $setting) {
                list($key, $value) = explode('=', $setting];
                if (strpos($envContent, $key . '=') === false) {
                    $envContent .= "\n" . $setting;
                } else {
                    $envContent = preg_replace("/^{$key}=.*/m", $setting, $envContent];
                }
            }
            
            file_put_contents($envFile, $envContent];
            echo "�?环境变量已更新\n";
        }
    }
    
    /**
     * 优化.htaccess文件
     */
    private function optimizeHtaccess(): void 
    {
        echo "🔒 优化.htaccess安全配置...\n";
        
        $htaccessPath = __DIR__ . '/../public/.htaccess';
        $htaccessContent = "# AlingAi Pro Production .htaccess
# 安全和性能优化配置

# 启用重写引擎
RewriteEngine On

# 隐藏PHP版本信息
ServerTokens Prod
Header unset X-Powered-By
Header always unset X-Powered-By

# 防止访问敏感文件
<FilesMatch \"\\.(env|ini|log|sh|sql)$\">
    Order deny,allow
    Deny from all
</FilesMatch>

# 防止目录浏览
Options -Indexes

# GZIP压缩
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE text/javascript
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE image/x-icon
    AddOutputFilterByType DEFLATE image/svg+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/x-font
    AddOutputFilterByType DEFLATE application/x-font-truetype
    AddOutputFilterByType DEFLATE application/x-font-ttf
    AddOutputFilterByType DEFLATE application/x-font-otf
    AddOutputFilterByType DEFLATE application/x-font-opentype
    AddOutputFilterByType DEFLATE application/vnd.ms-fontobject
    AddOutputFilterByType DEFLATE font/ttf
    AddOutputFilterByType DEFLATE font/otf
    AddOutputFilterByType DEFLATE font/opentype
</IfModule>

# 浏览器缓�?
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css \"access plus 1 month\"
    ExpiresByType application/javascript \"access plus 1 month\"
    ExpiresByType image/png \"access plus 1 month\"
    ExpiresByType image/jpg \"access plus 1 month\"
    ExpiresByType image/jpeg \"access plus 1 month\"
    ExpiresByType image/gif \"access plus 1 month\"
    ExpiresByType image/ico \"access plus 1 month\"
    ExpiresByType image/icon \"access plus 1 month\"
    ExpiresByType text/html \"access plus 300 seconds\"
</IfModule>

# 安全�?
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection \"1; mode=block\"
    Header always set Strict-Transport-Security \"max-age=31536000; includeSubDomains\"
    Header always set Referrer-Policy \"strict-origin-when-cross-origin\"
    Header always set Content-Security-Policy \"default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self' https://api.deepseek.com\"
</IfModule>

# API路由重写
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/(.*)$ /api/index.php [QSA,L]

# 主应用路�?
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /index.php [QSA,L]
";
        
        file_put_contents($htaccessPath, $htaccessContent];
        echo "�?.htaccess已优化\n";
    }
    
    /**
     * 验证配置
     */
    public function validateProductionConfig(): array 
    {
        echo "🔍 验证生产环境配置...\n";
        
        $results = [];
        
        // 检查错误显�?
        $results['error_display'] = ini_get('display_errors') == '0';
        
        // 检查错误日�?
        $results['error_logging'] = ini_get('log_errors') == '1';
        
        // 检查安全设�?
        $results['expose_php'] = ini_get('expose_php') == '0';
        
        // 检查环境变�?
        $results['app_env'] = ($_ENV['APP_ENV'] ?? 'development') === 'production';
        
        // 检�?htaccess
        $results['htaccess'] = file_exists(__DIR__ . '/../public/.htaccess'];
        
        foreach ($results as $check => $passed) {
            $status = $passed ? '�? : '�?;
            echo "{$status} {$check}: " . ($passed ? '通过' : '失败') . "\n";
        }
        
        return $results;
    }
}

// 如果直接运行此脚�?
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $optimizer = new ProductionOptimizer(];
    $optimizer->applyProductionConfig(];
    $optimizer->validateProductionConfig(];
}
