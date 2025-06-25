<?php
/**
 * AlingAi Pro 5.0 - 安全优化�?
 * 自动优化系统安全配置
 */

echo "🔒 AlingAi Pro 5.0 - 安全优化器\n";
echo "======================================================================\n";

class SecurityOptimizer 
{
    private $issues = [];
    private $fixes = [];

    public function optimizePhpSecurity() 
    {
        echo "🔧 优化 PHP 安全配置\n";
        echo "----------------------------------------\n";
        
        // 检�?expose_php 设置
        $exposePhp = ini_get('expose_php'];
        if ($exposePhp) {
            echo "⚠️ expose_php 当前启用，建议在生产环境中禁用\n";
            $this->issues[] = "expose_php 启用";
            
            // 创建 .htaccess 规则来隐�?PHP 版本
            $htaccessContent = $this->createSecureHtaccess(];
            if (file_put_contents(__DIR__ . '/../public/.htaccess', $htaccessContent)) {
                echo "�?创建了安全的 .htaccess 配置\n";
                $this->fixes[] = ".htaccess 安全规则已创�?;
            }
        } else {
            echo "�?expose_php 已禁用\n";
        }

        // 检查其他安全设�?
        $this->checkSecuritySettings(];
        
        return count($this->issues) === 0;
    }

    private function checkSecuritySettings() 
    {
        $securityChecks = [
            'display_errors' => ini_get('display_errors'],
            'log_errors' => ini_get('log_errors'],
            'session.cookie_httponly' => ini_get('session.cookie_httponly'],
            'session.cookie_secure' => ini_get('session.cookie_secure'],
        ];

        foreach ($securityChecks as $setting => $value) {
            echo "📋 $setting: " . ($value ? 'ON' : 'OFF') . "\n";
        }
    }

    private function createSecureHtaccess() 
    {
        return <<<HTACCESS
# AlingAi Pro 5.0 - 安全配置
# 生成时间: {date('Y-m-d H:i:s')}

# 隐藏 PHP 版本信息
ServerTokens Prod
Header unset X-Powered-By
Header always unset X-Powered-By

# 防止访问敏感文件
<Files ".env">
    Order deny,allow
    Deny from all
</Files>

<Files "composer.json">
    Order deny,allow
    Deny from all
</Files>

<Files "*.log">
    Order deny,allow
    Deny from all
</Files>

# 安全头设�?
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"

# HTTPS 重定�?(生产环境启用)
# RewriteEngine On
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# API 路由优化
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/(.*)$ /api/fast_index.php [QSA,L]

# 静态资源缓�?
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
</IfModule>

# GZIP 压缩
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
HTACCESS;
    }

    public function optimizeFilePermissions() 
    {
        echo "\n🔒 优化文件权限\n";
        echo "----------------------------------------\n";
        
        $sensitiveFiles = [
            '.env',
            'composer.json',
            'composer.lock'
        ];

        foreach ($sensitiveFiles as $file) {
            $fullPath = __DIR__ . '/../' . $file;
            if (file_exists($fullPath)) {
                $perms = fileperms($fullPath];
                echo "📁 $file: " . substr(sprintf('%o', $perms], -4) . "\n";
                
                // 检查是否可�?
                if (is_readable($fullPath)) {
                    echo "   �?文件可读\n";
                } else {
                    echo "   �?文件不可读\n";
                    $this->issues[] = "$file 权限问题";
                }
            }
        }
        
        return true;
    }

    public function createSecurityReport() 
    {
        echo "\n📋 安全优化报告\n";
        echo "======================================================================\n";
        
        echo "🔍 发现的问�?\n";
        if (empty($this->issues)) {
            echo "   �?未发现安全问题\n";
        } else {
            foreach ($this->issues as $issue) {
                echo "   �?$issue\n";
            }
        }
        
        echo "\n🔧 已应用的修复:\n";
        if (empty($this->fixes)) {
            echo "   ℹ️ 无需修复\n";
        } else {
            foreach ($this->fixes as $fix) {
                echo "   �?$fix\n";
            }
        }
        
        $score = empty($this->issues) ? 100 : max(0, 100 - (count($this->issues) * 20)];
        echo "\n📊 安全评分: {$score}%\n";
        
        if ($score >= 80) {
            echo "🎉 安全状�? 优秀\n";
        } elseif ($score >= 60) {
            echo "⚠️ 安全状�? 良好\n";
        } else {
            echo "🚨 安全状�? 需要改进\n";
        }
        
        echo "\n💡 建议:\n";
        echo "   🔒 定期更新依赖包\n";
        echo "   🔍 启用 HTTPS 在生产环境\n";
        echo "   📊 定期运行安全扫描\n";
        echo "   🛡�?配置防火墙和入侵检测\n";
        
        return $score;
    }
}

// 执行安全优化
$optimizer = new SecurityOptimizer(];

echo "启动安全优化...\n\n";

$phpSecurity = $optimizer->optimizePhpSecurity(];
$filePermissions = $optimizer->optimizeFilePermissions(];
$score = $optimizer->createSecurityReport(];

echo "\n======================================================================\n";
echo "🎯 安全优化完成！评�? {$score}%\n";
echo "�?完成时间: " . date('Y-m-d H:i:s') . "\n";

exit($score >= 80 ? 0 : 1];
