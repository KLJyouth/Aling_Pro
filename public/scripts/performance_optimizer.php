<?php
/**
 * AlingAi Pro 性能和负载优化配置脚�?
 * 根据实际负载调整缓存和数据库配置
 */

class PerformanceOptimizer
{
    private $config = [];
    private $recommendations = [];
    
    public function __construct()
    {
        echo "🚀 AlingAi Pro 性能优化开�?..\n";
        echo "优化时间: " . date('Y-m-d H:i:s') . "\n\n";
        $this->loadEnvironmentConfig(];
    }
    
    public function runPerformanceOptimization()
    {
        $this->analyzeSystemResources(];
        $this->optimizeDatabaseConfig(];
        $this->optimizeCacheConfig(];
        $this->optimizeApplicationConfig(];
        $this->generateOptimizedConfigs(];
        $this->generatePerformanceReport(];
    }
    
    private function loadEnvironmentConfig()
    {
        if (file_exists('../.env')) {
            $lines = file('../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES];
            foreach ($lines as $line) {
                if (strpos(trim($line], '#') === 0) continue;
                if (strpos($line, '=') !== false) {
                    list($name, $value) = explode('=', $line, 2];
                    $this->config[trim($name)] = trim($value, "\" \t\n\r\0\x0B"];
                }
            }
        }
    }
    
    private function analyzeSystemResources()
    {
        echo "📊 分析系统资源...\n";
        
        // 获取系统内存信息
        $memInfo = $this->getSystemMemory(];
        $this->recommendations['memory'] = $memInfo;
        
        // 获取CPU信息
        $cpuInfo = $this->getCPUInfo(];
        $this->recommendations['cpu'] = $cpuInfo;
        
        // 分析磁盘性能
        $diskInfo = $this->getDiskInfo(];
        $this->recommendations['disk'] = $diskInfo;
        
        echo "  �?系统资源分析完成\n\n";
    }
    
    private function getSystemMemory()
    {
        $memory = [];
        
        if (PHP_OS_FAMILY === 'Windows') {
            // Windows 系统内存检�?
            $output = shell_exec('wmic computersystem get TotalPhysicalMemory /value'];
            if (preg_match('/TotalPhysicalMemory=(\d+)/', $output, $matches)) {
                $totalMemory = round($matches[1] / 1024 / 1024 / 1024, 2]; // GB
                $memory['total'] = $totalMemory;
                
                if ($totalMemory >= 16) {
                    $memory['recommendation'] = 'high_performance';
                    $memory['php_memory'] = '512M';
                    $memory['cache_size'] = '1024MB';
                } elseif ($totalMemory >= 8) {
                    $memory['recommendation'] = 'medium_performance';
                    $memory['php_memory'] = '256M';
                    $memory['cache_size'] = '512MB';
                } else {
                    $memory['recommendation'] = 'basic_performance';
                    $memory['php_memory'] = '128M';
                    $memory['cache_size'] = '256MB';
                }
            }
        } else {
            // Linux 系统内存检�?
            if (file_exists('/proc/meminfo')) {
                $memInfo = file_get_contents('/proc/meminfo'];
                if (preg_match('/MemTotal:\s+(\d+) kB/', $memInfo, $matches)) {
                    $totalMemory = round($matches[1] / 1024 / 1024, 2]; // GB
                    $memory['total'] = $totalMemory;
                }
            }
        }
        
        return $memory;
    }
    
    private function getCPUInfo()
    {
        $cpu = [];
        
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('wmic cpu get NumberOfCores,NumberOfLogicalProcessors /value'];
            if (preg_match('/NumberOfCores=(\d+)/', $output, $matches)) {
                $cpu['cores'] = (int)$matches[1];
            }
            if (preg_match('/NumberOfLogicalProcessors=(\d+)/', $output, $matches)) {
                $cpu['threads'] = (int)$matches[1];
            }
        }
        
        // 根据CPU性能推荐配置
        $cores = $cpu['cores'] ?? 4;
        if ($cores >= 8) {
            $cpu['recommendation'] = 'high_concurrency';
            $cpu['worker_processes'] = $cores;
            $cpu['max_connections'] = 1000;
        } elseif ($cores >= 4) {
            $cpu['recommendation'] = 'medium_concurrency';
            $cpu['worker_processes'] = $cores;
            $cpu['max_connections'] = 500;
        } else {
            $cpu['recommendation'] = 'basic_concurrency';
            $cpu['worker_processes'] = 2;
            $cpu['max_connections'] = 200;
        }
        
        return $cpu;
    }
    
    private function getDiskInfo()
    {
        $disk = [];
        
        // 检测磁盘类型和性能
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('wmic diskdrive get MediaType,Size /value'];
            if (strpos($output, 'SSD') !== false) {
                $disk['type'] = 'SSD';
                $disk['recommendation'] = 'high_io';
            } else {
                $disk['type'] = 'HDD';
                $disk['recommendation'] = 'standard_io';
            }
        }
        
        return $disk;
    }
    
    private function optimizeDatabaseConfig()
    {
        echo "🗄�?优化数据库配�?..\n";
        
        $memory = $this->recommendations['memory'];
        $cpu = $this->recommendations['cpu'];
        
        $dbConfig = [];
        
        // 根据内存大小调整数据库配�?
        if ($memory['recommendation'] === 'high_performance') {
            $dbConfig = [
                'innodb_buffer_pool_size' => '1G',
                'innodb_log_file_size' => '256M',
                'max_connections' => 500,
                'query_cache_size' => '64M',
                'tmp_table_size' => '64M',
                'max_heap_table_size' => '64M'
            ];
        } elseif ($memory['recommendation'] === 'medium_performance') {
            $dbConfig = [
                'innodb_buffer_pool_size' => '512M',
                'innodb_log_file_size' => '128M',
                'max_connections' => 300,
                'query_cache_size' => '32M',
                'tmp_table_size' => '32M',
                'max_heap_table_size' => '32M'
            ];
        } else {
            $dbConfig = [
                'innodb_buffer_pool_size' => '256M',
                'innodb_log_file_size' => '64M',
                'max_connections' => 150,
                'query_cache_size' => '16M',
                'tmp_table_size' => '16M',
                'max_heap_table_size' => '16M'
            ];
        }
        
        $this->recommendations['database'] = $dbConfig;
        echo "  �?数据库配置优化完成\n\n";
    }
    
    private function optimizeCacheConfig()
    {
        echo "💾 优化缓存配置...\n";
        
        $memory = $this->recommendations['memory'];
        $cacheConfig = [];
        
        // Redis 配置优化
        $cacheConfig['redis'] = [
            'maxmemory' => $memory['cache_size'] ?? '256MB',
            'maxmemory_policy' => 'allkeys-lru',
            'timeout' => 300,
            'tcp_keepalive' => 300,
            'save_900' => 1,
            'save_300' => 10,
            'save_60' => 10000
        ];
        
        // PHP OPcache 配置
        $cacheConfig['opcache'] = [
            'opcache.memory_consumption' => substr($memory['php_memory'] ?? '128M', 0, -1],
            'opcache.max_accelerated_files' => 4000,
            'opcache.revalidate_freq' => 60,
            'opcache.enable_cli' => 1
        ];
        
        $this->recommendations['cache'] = $cacheConfig;
        echo "  �?缓存配置优化完成\n\n";
    }
    
    private function optimizeApplicationConfig()
    {
        echo "⚙️ 优化应用程序配置...\n";
        
        $cpu = $this->recommendations['cpu'];
        $memory = $this->recommendations['memory'];
        
        $appConfig = [
            'max_execution_time' => 60,
            'memory_limit' => $memory['php_memory'] ?? '128M',
            'post_max_size' => '50M',
            'upload_max_filesize' => '50M',
            'max_file_uploads' => 20,
            'max_input_vars' => 3000,
            'session_gc_maxlifetime' => 1440,
            'worker_processes' => $cpu['worker_processes'] ?? 2,
            'max_connections' => $cpu['max_connections'] ?? 200
        ];
        
        $this->recommendations['application'] = $appConfig;
        echo "  �?应用程序配置优化完成\n\n";
    }
    
    private function generateOptimizedConfigs()
    {
        echo "📝 生成优化配置文件...\n";
        
        // 生成 MySQL 配置文件
        $this->generateMySQLConfig(];
        
        // 生成 Redis 配置文件
        $this->generateRedisConfig(];
        
        // 生成 PHP 配置文件
        $this->generatePHPConfig(];
        
        // 生成 Nginx 配置文件
        $this->generateNginxConfig(];
        
        echo "  �?配置文件生成完成\n\n";
    }
    
    private function generateMySQLConfig()
    {
        $dbConfig = $this->recommendations['database'];
        
        $mysqlConfig = <<<EOF
# MySQL 性能优化配置 - AlingAi Pro
# 生成时间: {date('Y-m-d H:i:s')}

[mysqld]
# 基础配置
bind-address = 127.0.0.1
port = 3306
default-storage-engine = InnoDB

# 内存配置
innodb_buffer_pool_size = {$dbConfig['innodb_buffer_pool_size']}
query_cache_size = {$dbConfig['query_cache_size']}
tmp_table_size = {$dbConfig['tmp_table_size']}
max_heap_table_size = {$dbConfig['max_heap_table_size']}

# 连接配置
max_connections = {$dbConfig['max_connections']}
max_connect_errors = 10000
connect_timeout = 10
wait_timeout = 600
interactive_timeout = 600

# InnoDB 配置
innodb_log_file_size = {$dbConfig['innodb_log_file_size']}
innodb_log_buffer_size = 16M
innodb_flush_log_at_trx_commit = 2
innodb_file_per_table = 1
innodb_buffer_pool_instances = 1

# 查询缓存
query_cache_type = 1
query_cache_limit = 2M

# 慢查询日�?
slow_query_log = 1
long_query_time = 2
log_queries_not_using_indexes = 1

# 安全配置
sql_mode = STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO
local_infile = 0

EOF;
        
        file_put_contents('mysql.optimized.cnf', $mysqlConfig];
    }
    
    private function generateRedisConfig()
    {
        $cacheConfig = $this->recommendations['cache']['redis'];
        
        $redisConfig = <<<EOF
# Redis 性能优化配置 - AlingAi Pro
# 生成时间: {date('Y-m-d H:i:s')}

# 网络配置
bind 127.0.0.1
port 6380
timeout {$cacheConfig['timeout']}
tcp-keepalive {$cacheConfig['tcp_keepalive']}

# 内存配置
maxmemory {$cacheConfig['maxmemory']}
maxmemory-policy {$cacheConfig['maxmemory_policy']}

# 持久化配�?
save {$cacheConfig['save_900']} 1
save {$cacheConfig['save_300']} 10
save {$cacheConfig['save_60']} 10000
stop-writes-on-bgsave-error yes
rdbcompression yes
rdbchecksum yes

# 安全配置
requirepass SecureRedisPass2024!

# 性能配置
databases 16
hz 10
dynamic-hz yes

# 日志配置
loglevel notice
syslog-enabled no

EOF;
        
        file_put_contents('redis.optimized.conf', $redisConfig];
    }
    
    private function generatePHPConfig()
    {
        $appConfig = $this->recommendations['application'];
        $cacheConfig = $this->recommendations['cache']['opcache'];
        
        $phpConfig = <<<EOF
; PHP 性能优化配置 - AlingAi Pro
; 生成时间: {date('Y-m-d H:i:s')}

; 基础配置
max_execution_time = {$appConfig['max_execution_time']}
memory_limit = {$appConfig['memory_limit']}
post_max_size = {$appConfig['post_max_size']}
upload_max_filesize = {$appConfig['upload_max_filesize']}
max_file_uploads = {$appConfig['max_file_uploads']}
max_input_vars = {$appConfig['max_input_vars']}

; 会话配置
session.gc_maxlifetime = {$appConfig['session_gc_maxlifetime']}
session.cookie_secure = 1
session.cookie_httponly = 1
session.use_strict_mode = 1

; OPcache 配置
opcache.enable = 1
opcache.memory_consumption = {$cacheConfig['opcache.memory_consumption']}
opcache.max_accelerated_files = {$cacheConfig['opcache.max_accelerated_files']}
opcache.revalidate_freq = {$cacheConfig['opcache.revalidate_freq']}
opcache.enable_cli = {$cacheConfig['opcache.enable_cli']}
opcache.fast_shutdown = 1
opcache.validate_timestamps = 0

; 错误报告
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log

; 安全配置
expose_php = Off
allow_url_fopen = Off
allow_url_include = Off

EOF;
        
        file_put_contents('php.optimized.ini', $phpConfig];
    }
    
    private function generateNginxConfig()
    {
        $appConfig = $this->recommendations['application'];
        
        $nginxConfig = <<<EOF
# Nginx 性能优化配置 - AlingAi Pro
# 生成时间: {date('Y-m-d H:i:s')}

worker_processes {$appConfig['worker_processes']};
worker_connections {$appConfig['max_connections']};

events {
    use epoll;
    worker_connections {$appConfig['max_connections']};
    multi_accept on;
}

http {
    # 基础配置
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    server_tokens off;
    
    # Gzip 压缩
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
    
    # 缓存配置
    open_file_cache max=10000 inactive=20s;
    open_file_cache_valid 30s;
    open_file_cache_min_uses 2;
    open_file_cache_errors on;
    
    # 安全配置
    add_header X-Frame-Options DENY;
    add_header X-Content-Type-Options nosniff;
    add_header X-XSS-Protection "1; mode=block";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains";
    
    # 限速配�?
    limit_req_zone \$binary_remote_addr zone=api:10m rate=10r/s;
    limit_conn_zone \$binary_remote_addr zone=conn_limit_per_ip:10m;
    
    server {
        listen 443 ssl http2;
        server_name your-domain.com;
        
        # SSL 配置
        ssl_certificate /path/to/ssl/cert.pem;
        ssl_certificate_key /path/to/ssl/key.pem;
        ssl_protocols TLSv1.2 TLSv1.3;
        ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
        
        # 限制配置
        limit_req zone=api burst=20 nodelay;
        limit_conn conn_limit_per_ip 10;
        
        root /path/to/alingai/public;
        index index.php;
        
        location / {
            try_files \$uri \$uri/ /index.php?\$query_string;
        }
        
        location ~ \.php\$ {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
            include fastcgi_params;
            
            # PHP 性能配置
            fastcgi_buffers 16 16k;
            fastcgi_buffer_size 32k;
            fastcgi_read_timeout 300;
        }
        
        # 静态文件缓�?
        location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)\$ {
            expires 1y;
            add_header Cache-Control "public, immutable";
        }
    }
}

EOF;
        
        file_put_contents('nginx.optimized.conf', $nginxConfig];
    }
    
    private function generatePerformanceReport()
    {
        echo "📊 生成性能优化报告...\n";
        echo str_repeat("=", 60) . "\n";
        echo "🚀 AlingAi Pro 性能优化报告\n";
        echo str_repeat("=", 60) . "\n";
        echo "优化时间: " . date('Y-m-d H:i:s') . "\n\n";
        
        echo "💻 系统资源分析:\n";
        echo str_repeat("-", 40) . "\n";
        $memory = $this->recommendations['memory'];
        $cpu = $this->recommendations['cpu'];
        
        echo "  内存总量: " . ($memory['total'] ?? '未知') . " GB\n";
        echo "  性能级别: " . ($memory['recommendation'] ?? '未知') . "\n";
        echo "  CPU 核心: " . ($cpu['cores'] ?? '未知') . " 核\n";
        echo "  并发级别: " . ($cpu['recommendation'] ?? '未知') . "\n\n";
        
        echo "🗄�?数据库优化配�?\n";
        echo str_repeat("-", 40) . "\n";
        $dbConfig = $this->recommendations['database'];
        foreach ($dbConfig as $key => $value) {
            echo "  {$key}: {$value}\n";
        }
        echo "\n";
        
        echo "💾 缓存优化配置:\n";
        echo str_repeat("-", 40) . "\n";
        $cacheConfig = $this->recommendations['cache'];
        echo "  Redis 最大内�? " . $cacheConfig['redis']['maxmemory'] . "\n";
        echo "  OPcache 内存: " . $cacheConfig['opcache']['opcache.memory_consumption'] . "M\n\n";
        
        echo "⚙️ 应用程序优化配置:\n";
        echo str_repeat("-", 40) . "\n";
        $appConfig = $this->recommendations['application'];
        echo "  PHP 内存限制: " . $appConfig['memory_limit'] . "\n";
        echo "  最大连接数: " . $appConfig['max_connections'] . "\n";
        echo "  工作进程�? " . $appConfig['worker_processes'] . "\n\n";
        
        echo "📁 生成的配置文�?\n";
        echo str_repeat("-", 40) . "\n";
        echo "  �?mysql.optimized.cnf - MySQL 优化配置\n";
        echo "  �?redis.optimized.conf - Redis 优化配置\n";
        echo "  �?php.optimized.ini - PHP 优化配置\n";
        echo "  �?nginx.optimized.conf - Nginx 优化配置\n\n";
        
        echo "💡 应用建议:\n";
        echo str_repeat("-", 40) . "\n";
        echo "  1. 在测试环境中验证所有配置\n";
        echo "  2. 逐步应用配置，监控性能变化\n";
        echo "  3. 根据实际负载调整参数\n";
        echo "  4. 定期监控系统资源使用情况\n";
        echo "  5. 建议配置系统监控和告警\n\n";
        
        echo str_repeat("=", 60) . "\n";
        
        // 保存详细报告
        $reportData = [
            'timestamp' => date('Y-m-d H:i:s'],
            'system_analysis' => [
                'memory' => $memory,
                'cpu' => $cpu
            ], 
            'optimizations' => $this->recommendations,
            'config_files' => [
                'mysql.optimized.cnf',
                'redis.optimized.conf', 
                'php.optimized.ini',
                'nginx.optimized.conf'
            ]
        ];
        
        file_put_contents('performance_optimization_report_' . date('Y_m_d_H_i_s') . '.json',
                         json_encode($reportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)];
        
        echo "📄 详细报告已保存到: performance_optimization_report_" . date('Y_m_d_H_i_s') . ".json\n";
    }
}

// 执行性能优化
$optimizer = new PerformanceOptimizer(];
$optimizer->runPerformanceOptimization(];
