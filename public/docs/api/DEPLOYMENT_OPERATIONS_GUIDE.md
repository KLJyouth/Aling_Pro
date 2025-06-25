# 🚀 AlingAi Pro 6.0 - 部署和运维指南

## 📋 概述

本指南提供AlingAi Pro 6.0零信任量子加密系统的完整部署和运维指导，涵盖开发、测试、预生产和生产环境的部署配置、性能优化、监控告警和故障处理。

---

## 🎯 环境规划

### 环境架构

```
开发环境 (Development)
├── 本地开发环境
├── 功能测试环境
└── 单元测试环境

测试环境 (Testing)
├── 集成测试环境
├── 性能测试环境
└── 安全测试环境

预生产环境 (Staging)
├── 生产仿真环境
├── 用户验收测试环境
└── 压力测试环境

生产环境 (Production)
├── 主生产环境
├── 灾备环境
└── 边缘节点环境
```

### 服务器规格建议

#### 开发/测试环境
```yaml
CPU: 4核心
内存: 8GB
存储: 100GB SSD
网络: 100Mbps
操作系统: Ubuntu 22.04 LTS / CentOS 8
```

#### 生产环境 - 小型部署
```yaml
应用服务器:
  CPU: 8核心 (Intel Xeon 或 AMD EPYC)
  内存: 16GB
  存储: 200GB NVMe SSD
  网络: 1Gbps

数据库服务器:
  CPU: 16核心
  内存: 32GB
  存储: 500GB NVMe SSD (RAID 10)
  网络: 1Gbps

缓存服务器:
  CPU: 4核心
  内存: 16GB
  存储: 100GB SSD
  网络: 1Gbps
```

#### 生产环境 - 大型部署
```yaml
负载均衡器: (2台，高可用)
  CPU: 8核心
  内存: 16GB
  网络: 10Gbps

应用服务器集群: (4台，横向扩展)
  CPU: 16核心
  内存: 32GB
  存储: 500GB NVMe SSD
  网络: 10Gbps

数据库集群: (主从复制 + 读写分离)
  主库: 32核心，64GB内存，1TB NVMe SSD
  从库: 16核心，32GB内存，500GB NVMe SSD

缓存集群: (Redis Cluster)
  3台缓存节点，每台8核心，32GB内存
```

---

## 🐳 容器化部署

### Docker环境配置

#### 1. 基础镜像构建
```dockerfile
# Dockerfile.prod
FROM php:8.2-fpm-alpine

# 安装系统依赖
RUN apk add --no-cache \
    git \
    zip \
    unzip \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    openssl-dev \
    libzip-dev \
    postgresql-dev \
    redis \
    supervisor

# 安装PHP扩展
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        mbstring \
        xml \
        zip \
        bcmath \
        opcache \
        pcntl \
        sockets

# 安装Redis扩展
RUN pecl install redis && docker-php-ext-enable redis

# 安装GMP扩展（SM2算法需要）
RUN apk add gmp-dev && docker-php-ext-install gmp

# 安装Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 配置工作目录
WORKDIR /app

# 复制应用代码
COPY . /app

# 安装依赖
RUN composer install --no-dev --optimize-autoloader

# 设置权限
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# 复制配置文件
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 暴露端口
EXPOSE 9000

# 启动命令
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

#### 2. 生产环境 Docker Compose
```yaml
# docker-compose.prod.yml
version: '3.8'

services:
  # Nginx反向代理
  nginx:
    image: nginx:alpine
    container_name: alingai-nginx
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./docker/nginx/prod.conf:/etc/nginx/nginx.conf:ro
      - ./public:/app/public:ro
      - ./ssl:/etc/ssl/certs:ro
    depends_on:
      - app
    networks:
      - alingai-network
    restart: unless-stopped
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost/health"]
      interval: 30s
      timeout: 10s
      retries: 3

  # PHP应用服务器
  app:
    build:
      context: .
      dockerfile: Dockerfile.prod
    container_name: alingai-app
    environment:
      - APP_ENV=production
      - DB_HOST=mysql
      - REDIS_HOST=redis
      - PHP_OPCACHE_ENABLE=1
      - PHP_MEMORY_LIMIT=512M
    volumes:
      - ./storage:/app/storage
      - ./bootstrap/cache:/app/bootstrap/cache
    depends_on:
      mysql:
        condition: service_healthy
      redis:
        condition: service_healthy
    networks:
      - alingai-network
    restart: unless-stopped
    healthcheck:
      test: ["CMD", "php", "/app/artisan", "health:check"]
      interval: 30s
      timeout: 10s
      retries: 3

  # MySQL数据库
  mysql:
    image: mysql:8.0
    container_name: alingai-mysql
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
    volumes:
      - mysql-data:/var/lib/mysql
      - ./docker/mysql/custom.cnf:/etc/mysql/conf.d/custom.cnf:ro
      - ./docker/mysql/init:/docker-entrypoint-initdb.d:ro
    networks:
      - alingai-network
    restart: unless-stopped
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-p${DB_ROOT_PASSWORD}"]
      interval: 30s
      timeout: 10s
      retries: 5

  # Redis缓存
  redis:
    image: redis:7-alpine
    container_name: alingai-redis
    command: redis-server /etc/redis/redis.conf
    volumes:
      - redis-data:/data
      - ./docker/redis/prod.conf:/etc/redis/redis.conf:ro
    networks:
      - alingai-network
    restart: unless-stopped
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 30s
      timeout: 10s
      retries: 3

  # 任务队列处理器
  queue:
    build:
      context: .
      dockerfile: Dockerfile.prod
    container_name: alingai-queue
    command: php /app/artisan queue:work --sleep=3 --tries=3
    environment:
      - APP_ENV=production
      - DB_HOST=mysql
      - REDIS_HOST=redis
    volumes:
      - ./storage:/app/storage
    depends_on:
      - mysql
      - redis
    networks:
      - alingai-network
    restart: unless-stopped

  # 计划任务调度器
  scheduler:
    build:
      context: .
      dockerfile: Dockerfile.prod
    container_name: alingai-scheduler
    command: php /app/artisan schedule:work
    environment:
      - APP_ENV=production
      - DB_HOST=mysql
      - REDIS_HOST=redis
    volumes:
      - ./storage:/app/storage
    depends_on:
      - mysql
      - redis
    networks:
      - alingai-network
    restart: unless-stopped

networks:
  alingai-network:
    driver: bridge
    ipam:
      config:
        - subnet: 172.20.0.0/16

volumes:
  mysql-data:
    driver: local
  redis-data:
    driver: local
```

### Kubernetes部署

#### 1. 命名空间配置
```yaml
# k8s/namespace.yaml
apiVersion: v1
kind: Namespace
metadata:
  name: alingai-pro
  labels:
    name: alingai-pro
    environment: production
```

#### 2. 应用部署配置
```yaml
# k8s/deployment.yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: alingai-app
  namespace: alingai-pro
  labels:
    app: alingai-app
spec:
  replicas: 3
  strategy:
    type: RollingUpdate
    rollingUpdate:
      maxSurge: 1
      maxUnavailable: 0
  selector:
    matchLabels:
      app: alingai-app
  template:
    metadata:
      labels:
        app: alingai-app
    spec:
      containers:
      - name: alingai-app
        image: alingai/alingai-pro:6.0.0
        ports:
        - containerPort: 9000
        env:
        - name: APP_ENV
          value: "production"
        - name: DB_HOST
          valueFrom:
            secretKeyRef:
              name: database-secret
              key: host
        - name: DB_PASSWORD
          valueFrom:
            secretKeyRef:
              name: database-secret
              key: password
        resources:
          requests:
            memory: "512Mi"
            cpu: "500m"
          limits:
            memory: "1Gi"
            cpu: "1000m"
        livenessProbe:
          httpGet:
            path: /health
            port: 9000
          initialDelaySeconds: 30
          periodSeconds: 10
        readinessProbe:
          httpGet:
            path: /ready
            port: 9000
          initialDelaySeconds: 5
          periodSeconds: 5
        volumeMounts:
        - name: storage
          mountPath: /app/storage
        - name: config
          mountPath: /app/config/production
      volumes:
      - name: storage
        persistentVolumeClaim:
          claimName: alingai-storage
      - name: config
        configMap:
          name: alingai-config
```

#### 3. 服务和Ingress配置
```yaml
# k8s/service.yaml
apiVersion: v1
kind: Service
metadata:
  name: alingai-app-service
  namespace: alingai-pro
spec:
  selector:
    app: alingai-app
  ports:
  - protocol: TCP
    port: 80
    targetPort: 9000
  type: ClusterIP

---
# k8s/ingress.yaml
apiVersion: networking.k8s.io/v1
kind: Ingress
metadata:
  name: alingai-ingress
  namespace: alingai-pro
  annotations:
    kubernetes.io/ingress.class: nginx
    cert-manager.io/cluster-issuer: letsencrypt-prod
    nginx.ingress.kubernetes.io/ssl-redirect: "true"
    nginx.ingress.kubernetes.io/force-ssl-redirect: "true"
spec:
  tls:
  - hosts:
    - api.alingai.com
    secretName: alingai-tls
  rules:
  - host: api.alingai.com
    http:
      paths:
      - path: /
        pathType: Prefix
        backend:
          service:
            name: alingai-app-service
            port:
              number: 80
```

---

## ⚙️ 系统配置优化

### Nginx配置优化

```nginx
# docker/nginx/prod.conf
user nginx;
worker_processes auto;
error_log /var/log/nginx/error.log warn;
pid /var/run/nginx.pid;

events {
    worker_connections 4096;
    use epoll;
    multi_accept on;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    # 日志格式
    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for" '
                    'rt=$request_time uct="$upstream_connect_time" '
                    'uht="$upstream_header_time" urt="$upstream_response_time"';

    access_log /var/log/nginx/access.log main;

    # 性能优化
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    server_tokens off;
    
    # Gzip压缩
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/json
        application/javascript
        application/xml+rss
        application/atom+xml;

    # 缓冲区设置
    client_body_buffer_size 128k;
    client_max_body_size 100m;
    client_header_buffer_size 1k;
    large_client_header_buffers 4 4k;
    output_buffers 1 32k;
    postpone_output 1460;

    # 连接池
    upstream alingai_backend {
        least_conn;
        server app:9000 weight=1 max_fails=3 fail_timeout=30s;
        keepalive 32;
    }

    # 限流配置
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
    limit_req_zone $binary_remote_addr zone=auth:10m rate=5r/s;

    server {
        listen 80;
        listen [::]:80;
        server_name api.alingai.com;
        return 301 https://$server_name$request_uri;
    }

    server {
        listen 443 ssl http2;
        listen [::]:443 ssl http2;
        server_name api.alingai.com;
        root /app/public;
        index index.php;

        # SSL配置
        ssl_certificate /etc/ssl/certs/alingai.crt;
        ssl_certificate_key /etc/ssl/certs/alingai.key;
        ssl_session_timeout 1d;
        ssl_session_cache shared:SSL:50m;
        ssl_session_tickets off;

        # 现代SSL配置
        ssl_protocols TLSv1.2 TLSv1.3;
        ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256;
        ssl_prefer_server_ciphers off;

        # HSTS
        add_header Strict-Transport-Security "max-age=63072000" always;

        # 安全头
        add_header X-Frame-Options DENY;
        add_header X-Content-Type-Options nosniff;
        add_header X-XSS-Protection "1; mode=block";
        add_header Referrer-Policy "strict-origin-when-cross-origin";

        # 静态文件缓存
        location ~* \.(jpg|jpeg|png|gif|ico|css|js|pdf|txt)$ {
            expires 1y;
            add_header Cache-Control "public, immutable";
        }

        # API限流
        location /api/auth {
            limit_req zone=auth burst=10 nodelay;
            try_files $uri $uri/ /index.php?$query_string;
        }

        location /api/ {
            limit_req zone=api burst=20 nodelay;
            try_files $uri $uri/ /index.php?$query_string;
        }

        # PHP处理
        location ~ \.php$ {
            try_files $uri =404;
            fastcgi_split_path_info ^(.+\.php)(/.+)$;
            fastcgi_pass alingai_backend;
            fastcgi_index index.php;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_param PATH_INFO $fastcgi_path_info;
            
            # FastCGI优化
            fastcgi_buffer_size 128k;
            fastcgi_buffers 4 256k;
            fastcgi_busy_buffers_size 256k;
            fastcgi_connect_timeout 60s;
            fastcgi_send_timeout 60s;
            fastcgi_read_timeout 60s;
        }

        # 隐藏敏感文件
        location ~ /\. {
            deny all;
        }

        location ~ /(composer|package)\.json$ {
            deny all;
        }
    }
}
```

### MySQL配置优化

```ini
# docker/mysql/custom.cnf
[mysqld]
# 基础设置
port = 3306
bind-address = 0.0.0.0
default-storage-engine = InnoDB
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# 连接设置
max_connections = 1000
max_connect_errors = 1000000
back_log = 500
thread_cache_size = 100
interactive_timeout = 300
wait_timeout = 300

# 缓冲区设置
innodb_buffer_pool_size = 8G
innodb_buffer_pool_instances = 8
innodb_log_file_size = 1G
innodb_log_files_in_group = 2
innodb_log_buffer_size = 256M

# 性能优化
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
innodb_io_capacity = 1000
innodb_io_capacity_max = 2000
innodb_read_io_threads = 8
innodb_write_io_threads = 8

# 查询缓存
query_cache_type = OFF
query_cache_size = 0

# 慢查询日志
slow_query_log = ON
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
log_queries_not_using_indexes = ON

# 二进制日志
log-bin = mysql-bin
binlog_format = ROW
expire_logs_days = 7
max_binlog_size = 100M

# 安全设置
skip-name-resolve
sql_mode = STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO

[mysql]
default-character-set = utf8mb4

[client]
default-character-set = utf8mb4
```

### Redis配置优化

```conf
# docker/redis/prod.conf
# 网络配置
bind 0.0.0.0
port 6379
tcp-backlog 511
timeout 300
tcp-keepalive 300

# 内存配置
maxmemory 4gb
maxmemory-policy allkeys-lru
maxmemory-samples 5

# 持久化配置
save 900 1
save 300 10
save 60 10000
stop-writes-on-bgsave-error yes
rdbcompression yes
rdbchecksum yes
dbfilename dump.rdb
dir /data

# AOF配置
appendonly yes
appendfilename "appendonly.aof"
appendfsync everysec
no-appendfsync-on-rewrite no
auto-aof-rewrite-percentage 100
auto-aof-rewrite-min-size 64mb

# 日志配置
loglevel notice
logfile /var/log/redis/redis.log

# 客户端配置
maxclients 10000

# 安全配置
requirepass your_redis_password
rename-command FLUSHDB ""
rename-command FLUSHALL ""
rename-command DEBUG ""
rename-command CONFIG "CONFIG_b835f8d7d8f7a8"

# 性能优化
hash-max-ziplist-entries 512
hash-max-ziplist-value 64
list-max-ziplist-size -2
list-compress-depth 0
set-max-intset-entries 512
zset-max-ziplist-entries 128
zset-max-ziplist-value 64
hll-sparse-max-bytes 3000
stream-node-max-bytes 4096
stream-node-max-entries 100
```

### PHP配置优化

```ini
# docker/php/php.ini
[PHP]
engine = On
short_open_tag = Off
precision = 14
output_buffering = 4096
zlib.output_compression = Off
implicit_flush = Off
unserialize_callback_func =
serialize_precision = -1
disable_functions = exec,passthru,shell_exec,system,proc_open,popen
disable_classes =
zend.enable_gc = On
zend.exception_ignore_args = On

# 资源限制
max_execution_time = 300
max_input_time = 300
memory_limit = 512M
post_max_size = 100M
upload_max_filesize = 100M
max_file_uploads = 20

# 错误处理
display_errors = Off
display_startup_errors = Off
log_errors = On
log_errors_max_len = 1024
ignore_repeated_errors = Off
ignore_repeated_source = Off
report_memleaks = On
error_log = /var/log/php/php_errors.log

# 数据处理
variables_order = "GPCS"
request_order = "GP"
register_argc_argv = Off
auto_globals_jit = On
default_mimetype = "text/html"
default_charset = "UTF-8"

# 路径和目录
include_path = ".:/usr/local/lib/php"
doc_root =
user_dir =
enable_dl = Off

# 文件上传
file_uploads = On
upload_tmp_dir = /tmp
max_file_uploads = 20

# 会话
session.save_handler = redis
session.save_path = "tcp://redis:6379?auth=your_redis_password"
session.use_strict_mode = 1
session.use_cookies = 1
session.use_only_cookies = 1
session.name = ALINGAI_SESSID
session.auto_start = 0
session.cookie_lifetime = 0
session.cookie_path = /
session.cookie_domain =
session.cookie_httponly = 1
session.cookie_secure = 1
session.cookie_samesite = Strict
session.serialize_handler = php_serialize
session.gc_probability = 1
session.gc_divisor = 1000
session.gc_maxlifetime = 1440

# 安全
allow_url_fopen = Off
allow_url_include = Off
expose_php = Off
```

```ini
# docker/php/opcache.ini
[opcache]
; 启用OPcache
opcache.enable = 1
opcache.enable_cli = 1

; 内存设置
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 10000

; 性能设置
opcache.revalidate_freq = 0
opcache.validate_timestamps = 0
opcache.fast_shutdown = 1
opcache.save_comments = 0

; 文件缓存
opcache.file_cache = /tmp/opcache
opcache.file_cache_only = 0

; JIT设置 (PHP 8.0+)
opcache.jit_buffer_size = 100M
opcache.jit = tracing

; 日志设置
opcache.log_verbosity_level = 2
opcache.error_log = /var/log/php/opcache.log
```

---

## 📊 监控和告警

### 系统监控配置

#### 1. Prometheus配置
```yaml
# monitoring/prometheus.yml
global:
  scrape_interval: 15s
  evaluation_interval: 15s

rule_files:
  - "alert_rules.yml"

alerting:
  alertmanagers:
    - static_configs:
        - targets:
          - alertmanager:9093

scrape_configs:
  # AlingAi应用监控
  - job_name: 'alingai-app'
    static_configs:
      - targets: ['app:9000']
    metrics_path: '/metrics'
    scrape_interval: 30s

  # Nginx监控
  - job_name: 'nginx'
    static_configs:
      - targets: ['nginx:9113']

  # MySQL监控
  - job_name: 'mysql'
    static_configs:
      - targets: ['mysql-exporter:9104']

  # Redis监控
  - job_name: 'redis'
    static_configs:
      - targets: ['redis-exporter:9121']

  # 节点监控
  - job_name: 'node'
    static_configs:
      - targets: ['node-exporter:9100']
```

#### 2. 告警规则配置
```yaml
# monitoring/alert_rules.yml
groups:
  - name: alingai_alerts
    rules:
      # 应用告警
      - alert: ApplicationDown
        expr: up{job="alingai-app"} == 0
        for: 1m
        labels:
          severity: critical
        annotations:
          summary: "AlingAi应用服务下线"
          description: "应用服务已下线超过1分钟"

      - alert: HighErrorRate
        expr: rate(http_requests_total{status=~"5.."}[5m]) > 0.1
        for: 2m
        labels:
          severity: warning
        annotations:
          summary: "应用错误率过高"
          description: "5分钟内错误率超过10%"

      - alert: HighResponseTime
        expr: histogram_quantile(0.95, rate(http_request_duration_seconds_bucket[5m])) > 1
        for: 5m
        labels:
          severity: warning
        annotations:
          summary: "响应时间过长"
          description: "95%的请求响应时间超过1秒"

      # 系统资源告警
      - alert: HighCPUUsage
        expr: 100 - (avg by(instance) (rate(node_cpu_seconds_total{mode="idle"}[5m])) * 100) > 80
        for: 5m
        labels:
          severity: warning
        annotations:
          summary: "CPU使用率过高"
          description: "CPU使用率超过80%"

      - alert: HighMemoryUsage
        expr: (1 - (node_memory_MemAvailable_bytes / node_memory_MemTotal_bytes)) * 100 > 85
        for: 5m
        labels:
          severity: warning
        annotations:
          summary: "内存使用率过高"
          description: "内存使用率超过85%"

      - alert: DiskSpaceLow
        expr: (1 - (node_filesystem_avail_bytes / node_filesystem_size_bytes)) * 100 > 90
        for: 5m
        labels:
          severity: critical
        annotations:
          summary: "磁盘空间不足"
          description: "磁盘使用率超过90%"

      # 数据库告警
      - alert: MySQLDown
        expr: up{job="mysql"} == 0
        for: 1m
        labels:
          severity: critical
        annotations:
          summary: "MySQL数据库下线"
          description: "MySQL数据库已下线"

      - alert: MySQLSlowQueries
        expr: rate(mysql_global_status_slow_queries[5m]) > 10
        for: 2m
        labels:
          severity: warning
        annotations:
          summary: "MySQL慢查询过多"
          description: "慢查询数量超过阈值"

      - alert: MySQLConnections
        expr: mysql_global_status_threads_connected / mysql_global_variables_max_connections * 100 > 80
        for: 5m
        labels:
          severity: warning
        annotations:
          summary: "MySQL连接数过高"
          description: "MySQL连接数超过最大连接数的80%"

      # Redis告警
      - alert: RedisDown
        expr: up{job="redis"} == 0
        for: 1m
        labels:
          severity: critical
        annotations:
          summary: "Redis缓存下线"
          description: "Redis缓存服务已下线"

      - alert: RedisMemoryHigh
        expr: redis_memory_used_bytes / redis_memory_max_bytes * 100 > 90
        for: 5m
        labels:
          severity: warning
        annotations:
          summary: "Redis内存使用率过高"
          description: "Redis内存使用率超过90%"
```

### 日志监控

#### 1. Filebeat配置
```yaml
# monitoring/filebeat.yml
filebeat.inputs:
  - type: log
    enabled: true
    paths:
      - /app/storage/logs/*.log
    fields:
      service: alingai-app
    fields_under_root: true
    multiline.pattern: '^\[\d{4}-\d{2}-\d{2}'
    multiline.negate: true
    multiline.match: after

  - type: log
    enabled: true
    paths:
      - /var/log/nginx/access.log
    fields:
      service: nginx-access
    fields_under_root: true

  - type: log
    enabled: true
    paths:
      - /var/log/nginx/error.log
    fields:
      service: nginx-error
    fields_under_root: true

output.elasticsearch:
  hosts: ["elasticsearch:9200"]
  index: "alingai-logs-%{+yyyy.MM.dd}"

processors:
  - add_host_metadata:
      when.not.contains.tags: forwarded

logging.level: info
logging.to_files: true
logging.files:
  path: /var/log/filebeat
  name: filebeat
  keepfiles: 7
  permissions: 0644
```

#### 2. Logstash配置
```ruby
# monitoring/logstash.conf
input {
  beats {
    port => 5044
  }
}

filter {
  if [service] == "alingai-app" {
    grok {
      match => { "message" => "\[%{TIMESTAMP_ISO8601:timestamp}\] %{WORD:level}\: %{GREEDYDATA:log_message}" }
    }
    
    if [level] == "ERROR" or [level] == "CRITICAL" {
      mutate {
        add_tag => ["error"]
      }
    }
  }
  
  if [service] == "nginx-access" {
    grok {
      match => { "message" => '%{IPORHOST:clientip} - %{USER:ident} \[%{HTTPDATE:timestamp}\] "%{WORD:verb} %{DATA:request} HTTP/%{NUMBER:httpversion}" %{NUMBER:response:int} (?:-|%{NUMBER:bytes:int}) %{QS:referrer} %{QS:agent} rt=%{NUMBER:request_time:float}' }
    }
    
    if [response] >= 400 {
      mutate {
        add_tag => ["http_error"]
      }
    }
    
    if [request_time] > 1.0 {
      mutate {
        add_tag => ["slow_request"]
      }
    }
  }
  
  date {
    match => [ "timestamp", "yyyy-MM-dd HH:mm:ss", "dd/MMM/yyyy:HH:mm:ss Z" ]
    target => "@timestamp"
  }
}

output {
  elasticsearch {
    hosts => ["elasticsearch:9200"]
    index => "alingai-logs-%{+YYYY.MM.dd}"
  }
  
  if "error" in [tags] or "http_error" in [tags] {
    stdout {
      codec => rubydebug
    }
  }
}
```

---

## 🔄 CI/CD流水线

### GitLab CI/CD配置

```yaml
# .gitlab-ci.yml
stages:
  - test
  - security
  - build
  - deploy
  - verify

variables:
  DOCKER_REGISTRY: registry.alingai.com
  DOCKER_IMAGE: alingai/alingai-pro
  KUBERNETES_NAMESPACE: alingai-pro

# 代码质量检查
code_quality:
  stage: test
  image: php:8.2-cli
  before_script:
    - apt-get update && apt-get install -y git zip unzip
    - curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    - composer install --dev
  script:
    - vendor/bin/phpstan analyse --level=8 src/
    - vendor/bin/php-cs-fixer fix --dry-run --diff
    - vendor/bin/phpunit --coverage-text --colors=never
  coverage: '/^\s*Lines:\s*\d+.\d+\%/'
  artifacts:
    reports:
      coverage_report:
        coverage_format: cobertura
        path: coverage.xml

# 安全扫描
security_scan:
  stage: security
  image: owasp/zap2docker-stable
  script:
    - zap-baseline.py -t http://localhost:8000 -J zap-report.json
  artifacts:
    reports:
      sast: zap-report.json
  allow_failure: true

# 构建镜像
build_image:
  stage: build
  image: docker:20.10.16
  services:
    - docker:20.10.16-dind
  before_script:
    - docker login -u $CI_REGISTRY_USER -p $CI_REGISTRY_PASSWORD $CI_REGISTRY
  script:
    - docker build -t $DOCKER_REGISTRY/$DOCKER_IMAGE:$CI_COMMIT_SHA -f Dockerfile.prod .
    - docker push $DOCKER_REGISTRY/$DOCKER_IMAGE:$CI_COMMIT_SHA
    - docker tag $DOCKER_REGISTRY/$DOCKER_IMAGE:$CI_COMMIT_SHA $DOCKER_REGISTRY/$DOCKER_IMAGE:latest
    - docker push $DOCKER_REGISTRY/$DOCKER_IMAGE:latest
  only:
    - main

# 部署到测试环境
deploy_staging:
  stage: deploy
  image: bitnami/kubectl:latest
  script:
    - kubectl config use-context staging
    - kubectl set image deployment/alingai-app alingai-app=$DOCKER_REGISTRY/$DOCKER_IMAGE:$CI_COMMIT_SHA -n $KUBERNETES_NAMESPACE
    - kubectl rollout status deployment/alingai-app -n $KUBERNETES_NAMESPACE
  environment:
    name: staging
    url: https://staging.api.alingai.com
  only:
    - main

# 自动化测试
integration_test:
  stage: verify
  image: node:16
  script:
    - npm install -g newman
    - newman run tests/postman/api-tests.json --environment tests/postman/staging.json
  dependencies:
    - deploy_staging
  only:
    - main

# 部署到生产环境
deploy_production:
  stage: deploy
  image: bitnami/kubectl:latest
  script:
    - kubectl config use-context production
    - kubectl set image deployment/alingai-app alingai-app=$DOCKER_REGISTRY/$DOCKER_IMAGE:$CI_COMMIT_SHA -n $KUBERNETES_NAMESPACE
    - kubectl rollout status deployment/alingai-app -n $KUBERNETES_NAMESPACE
  environment:
    name: production
    url: https://api.alingai.com
  when: manual
  only:
    - main

# 生产环境验证
production_verify:
  stage: verify
  image: curlimages/curl:latest
  script:
    - curl -f https://api.alingai.com/health || exit 1
    - curl -f https://api.alingai.com/metrics || exit 1
  dependencies:
    - deploy_production
  only:
    - main
```

### GitHub Actions配置

```yaml
# .github/workflows/ci-cd.yml
name: CI/CD Pipeline

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

env:
  REGISTRY: ghcr.io
  IMAGE_NAME: alingai/alingai-pro

jobs:
  test:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: secret
          MYSQL_DATABASE: alingai_test
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3
      redis:
        image: redis:7-alpine
        options: >-
          --health-cmd="redis-cli ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3

    steps:
    - uses: actions/checkout@v3

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: 8.2
        extensions: mbstring, xml, ctype, iconv, intl, pdo_mysql, redis, gmp
        tools: composer:v2

    - name: Cache Composer dependencies
      uses: actions/cache@v3
      with:
        path: /tmp/composer-cache
        key: ${{ runner.os }}-${{ hashFiles('**/composer.lock') }}

    - name: Install dependencies
      run: composer install --prefer-dist --no-progress

    - name: Copy environment file
      run: cp .env.testing .env

    - name: Generate application key
      run: php artisan key:generate

    - name: Run database migrations
      run: php artisan migrate --force

    - name: Execute tests
      run: vendor/bin/phpunit --coverage-clover coverage.xml

    - name: Upload coverage to Codecov
      uses: codecov/codecov-action@v3
      with:
        file: ./coverage.xml

  security:
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v3

    - name: Run Trivy vulnerability scanner
      uses: aquasecurity/trivy-action@master
      with:
        scan-type: 'fs'
        scan-ref: '.'
        format: 'sarif'
        output: 'trivy-results.sarif'

    - name: Upload Trivy scan results
      uses: github/codeql-action/upload-sarif@v2
      with:
        sarif_file: 'trivy-results.sarif'

  build:
    needs: [test, security]
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    steps:
    - uses: actions/checkout@v3

    - name: Log in to Container Registry
      uses: docker/login-action@v2
      with:
        registry: ${{ env.REGISTRY }}
        username: ${{ github.actor }}
        password: ${{ secrets.GITHUB_TOKEN }}

    - name: Build and push Docker image
      uses: docker/build-push-action@v4
      with:
        context: .
        file: ./Dockerfile.prod
        push: true
        tags: |
          ${{ env.REGISTRY }}/${{ env.IMAGE_NAME }}:${{ github.sha }}
          ${{ env.REGISTRY }}/${{ env.IMAGE_NAME }}:latest

  deploy:
    needs: build
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    steps:
    - name: Deploy to Kubernetes
      uses: azure/k8s-deploy@v1
      with:
        manifests: |
          k8s/deployment.yaml
          k8s/service.yaml
          k8s/ingress.yaml
        images: |
          ${{ env.REGISTRY }}/${{ env.IMAGE_NAME }}:${{ github.sha }}
        kubectl-version: 'latest'
```

---

## 🛠️ 故障处理和恢复

### 常见故障诊断

#### 1. 应用启动失败
```bash
# 检查容器状态
docker ps -a

# 查看容器日志
docker logs alingai-app

# 检查配置文件
docker exec alingai-app php artisan config:show

# 检查数据库连接
docker exec alingai-app php artisan db:check

# 检查文件权限
docker exec alingai-app ls -la storage/
```

#### 2. 数据库连接问题
```bash
# 检查MySQL状态
docker exec alingai-mysql mysqladmin ping

# 查看数据库日志
docker logs alingai-mysql

# 检查连接数
docker exec alingai-mysql mysql -u root -p -e "SHOW PROCESSLIST;"

# 检查锁等待
docker exec alingai-mysql mysql -u root -p -e "SHOW ENGINE INNODB STATUS\G" | grep -A 10 "LATEST DETECTED DEADLOCK"
```

#### 3. 缓存问题
```bash
# 检查Redis状态
docker exec alingai-redis redis-cli ping

# 查看Redis信息
docker exec alingai-redis redis-cli info

# 清理缓存
docker exec alingai-app php artisan cache:clear
docker exec alingai-app php artisan config:clear
docker exec alingai-app php artisan route:clear
```

#### 4. 性能问题诊断
```bash
# 检查系统资源
top
htop
iotop
vmstat 1

# 查看进程状态
ps aux | grep php
ps aux | grep nginx
ps aux | grep mysql

# 检查网络连接
netstat -tulpn
ss -tulpn

# 分析慢查询
docker exec alingai-mysql mysqldumpslow /var/log/mysql/slow.log
```

### 备份和恢复

#### 1. 数据库备份脚本
```bash
#!/bin/bash
# backup.sh

# 配置变量
DB_HOST="mysql"
DB_USER="backup_user"
DB_PASS="backup_password"
DB_NAME="alingai_pro"
BACKUP_DIR="/backup/mysql"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=7

# 创建备份目录
mkdir -p $BACKUP_DIR

# 执行备份
docker exec alingai-mysql mysqldump \
  --host=$DB_HOST \
  --user=$DB_USER \
  --password=$DB_PASS \
  --single-transaction \
  --routines \
  --triggers \
  --events \
  --create-options \
  --comments \
  --dump-date \
  $DB_NAME | gzip > $BACKUP_DIR/alingai_${DATE}.sql.gz

# 检查备份文件
if [ -f "$BACKUP_DIR/alingai_${DATE}.sql.gz" ]; then
    echo "备份成功: $BACKUP_DIR/alingai_${DATE}.sql.gz"
    
    # 验证备份文件
    gunzip -t $BACKUP_DIR/alingai_${DATE}.sql.gz
    if [ $? -eq 0 ]; then
        echo "备份文件验证成功"
    else
        echo "备份文件验证失败"
        exit 1
    fi
else
    echo "备份失败"
    exit 1
fi

# 清理旧备份
find $BACKUP_DIR -name "alingai_*.sql.gz" -mtime +$RETENTION_DAYS -delete
echo "清理了超过 $RETENTION_DAYS 天的旧备份文件"

# 上传到远程存储（可选）
# aws s3 cp $BACKUP_DIR/alingai_${DATE}.sql.gz s3://your-backup-bucket/mysql/
```

#### 2. 数据恢复脚本
```bash
#!/bin/bash
# restore.sh

# 检查参数
if [ $# -ne 1 ]; then
    echo "使用方法: $0 <备份文件路径>"
    exit 1
fi

BACKUP_FILE=$1
DB_HOST="mysql"
DB_USER="root"
DB_PASS="root_password"
DB_NAME="alingai_pro"

# 检查备份文件是否存在
if [ ! -f "$BACKUP_FILE" ]; then
    echo "错误: 备份文件不存在: $BACKUP_FILE"
    exit 1
fi

# 停止应用服务
echo "停止应用服务..."
docker stop alingai-app alingai-queue alingai-scheduler

# 创建恢复前备份
echo "创建恢复前备份..."
./backup.sh

# 执行恢复
echo "开始恢复数据库..."
if [[ $BACKUP_FILE == *.gz ]]; then
    gunzip -c $BACKUP_FILE | docker exec -i alingai-mysql mysql \
        --host=$DB_HOST \
        --user=$DB_USER \
        --password=$DB_PASS \
        $DB_NAME
else
    docker exec -i alingai-mysql mysql \
        --host=$DB_HOST \
        --user=$DB_USER \
        --password=$DB_PASS \
        $DB_NAME < $BACKUP_FILE
fi

if [ $? -eq 0 ]; then
    echo "数据库恢复成功"
else
    echo "数据库恢复失败"
    exit 1
fi

# 启动应用服务
echo "启动应用服务..."
docker start alingai-app alingai-queue alingai-scheduler

# 等待服务启动
sleep 30

# 验证恢复
echo "验证恢复结果..."
curl -f http://localhost/health
if [ $? -eq 0 ]; then
    echo "恢复验证成功，应用正常运行"
else
    echo "恢复验证失败，请检查应用状态"
fi
```

### 灾难恢复计划

#### 1. 恢复时间目标 (RTO)
- **关键业务**: 1小时内恢复
- **重要业务**: 4小时内恢复
- **一般业务**: 24小时内恢复

#### 2. 恢复点目标 (RPO)
- **数据库**: 15分钟内的数据损失
- **文件存储**: 1小时内的数据损失
- **配置文件**: 立即恢复（版本控制）

#### 3. 恢复步骤
```bash
# 1. 评估损害程度
./scripts/assess_damage.sh

# 2. 启动备用环境
kubectl apply -f k8s/disaster-recovery/

# 3. 恢复数据库
./scripts/restore_database.sh latest

# 4. 恢复应用文件
./scripts/restore_files.sh

# 5. 验证系统功能
./scripts/verify_system.sh

# 6. 切换DNS指向
./scripts/switch_dns.sh

# 7. 通知相关人员
./scripts/notify_recovery.sh
```

---

## 📈 性能优化和扩展

### 水平扩展策略

#### 1. 应用服务器扩展
```yaml
# k8s/hpa.yaml
apiVersion: autoscaling/v2
kind: HorizontalPodAutoscaler
metadata:
  name: alingai-app-hpa
  namespace: alingai-pro
spec:
  scaleTargetRef:
    apiVersion: apps/v1
    kind: Deployment
    name: alingai-app
  minReplicas: 3
  maxReplicas: 20
  metrics:
  - type: Resource
    resource:
      name: cpu
      target:
        type: Utilization
        averageUtilization: 70
  - type: Resource
    resource:
      name: memory
      target:
        type: Utilization
        averageUtilization: 80
  behavior:
    scaleUp:
      stabilizationWindowSeconds: 300
      policies:
      - type: Percent
        value: 100
        periodSeconds: 15
    scaleDown:
      stabilizationWindowSeconds: 300
      policies:
      - type: Percent
        value: 10
        periodSeconds: 60
```

#### 2. 数据库读写分离
```php
// config/database.php
'mysql' => [
    'write' => [
        'host' => env('DB_WRITE_HOST', 'mysql-master'),
        'port' => env('DB_WRITE_PORT', 3306),
        'database' => env('DB_DATABASE'),
        'username' => env('DB_USERNAME'),
        'password' => env('DB_PASSWORD'),
    ],
    'read' => [
        [
            'host' => env('DB_READ_HOST_1', 'mysql-slave-1'),
            'port' => env('DB_READ_PORT_1', 3306),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_READ_USERNAME'),
            'password' => env('DB_READ_PASSWORD'),
        ],
        [
            'host' => env('DB_READ_HOST_2', 'mysql-slave-2'),
            'port' => env('DB_READ_PORT_2', 3306),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_READ_USERNAME'),
            'password' => env('DB_READ_PASSWORD'),
        ],
    ],
    'sticky' => true,
]
```

#### 3. Redis集群配置
```conf
# redis-cluster.conf
cluster-enabled yes
cluster-config-file nodes.conf
cluster-node-timeout 5000
cluster-announce-ip 10.0.0.1
cluster-announce-port 7000
cluster-announce-bus-port 17000
```

### 缓存优化策略

#### 1. 多级缓存架构
```php
class MultiLevelCache
{
    private $l1Cache; // 本地缓存 (APCu)
    private $l2Cache; // Redis缓存
    private $l3Cache; // 分布式缓存 (Memcached)
    
    public function get(string $key, callable $callback = null, int $ttl = 3600)
    {
        // L1缓存查找
        $value = $this->l1Cache->get($key);
        if ($value !== false) {
            return $value;
        }
        
        // L2缓存查找
        $value = $this->l2Cache->get($key);
        if ($value !== false) {
            $this->l1Cache->set($key, $value, min($ttl, 300)); // L1缓存5分钟
            return $value;
        }
        
        // L3缓存查找
        $value = $this->l3Cache->get($key);
        if ($value !== false) {
            $this->l2Cache->setex($key, $ttl, $value);
            $this->l1Cache->set($key, $value, min($ttl, 300));
            return $value;
        }
        
        // 执行回调获取数据
        if ($callback) {
            $value = $callback();
            if ($value !== null) {
                $this->set($key, $value, $ttl);
            }
            return $value;
        }
        
        return null;
    }
    
    public function set(string $key, $value, int $ttl = 3600): void
    {
        $this->l3Cache->set($key, $value, $ttl);
        $this->l2Cache->setex($key, $ttl, $value);
        $this->l1Cache->set($key, $value, min($ttl, 300));
    }
}
```

#### 2. 智能缓存预热
```php
class CacheWarmer
{
    public function warmup(): void
    {
        $this->warmupUserSessions();
        $this->warmupFrequentQueries();
        $this->warmupStaticData();
    }
    
    private function warmupUserSessions(): void
    {
        // 预热活跃用户会话
        $activeUsers = User::where('last_activity', '>', now()->subHours(24))->get();
        foreach ($activeUsers as $user) {
            Cache::remember("user_permissions_{$user->id}", 3600, function() use ($user) {
                return $user->getAllPermissions();
            });
        }
    }
    
    private function warmupFrequentQueries(): void
    {
        // 预热频繁查询的数据
        Cache::remember('system_config', 86400, function() {
            return SystemConfig::all()->pluck('value', 'key');
        });
        
        Cache::remember('api_routes', 3600, function() {
            return Route::getRoutes();
        });
    }
}
```

### 数据库优化

#### 1. 查询优化
```sql
-- 创建必要的索引
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_status_created ON users(status, created_at);
CREATE INDEX idx_api_tokens_user_expires ON api_tokens(user_id, expires_at);
CREATE INDEX idx_logs_level_created ON logs(level, created_at);

-- 复合索引优化
CREATE INDEX idx_user_logs_compound ON user_logs(user_id, action, created_at DESC);

-- 全文索引
CREATE FULLTEXT INDEX idx_documents_content ON documents(title, content);
```

#### 2. 分区表设计
```sql
-- 按时间分区的日志表
CREATE TABLE user_logs (
    id BIGINT AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP NOT NULL,
    PRIMARY KEY (id, created_at),
    INDEX idx_user_action (user_id, action)
) PARTITION BY RANGE (UNIX_TIMESTAMP(created_at)) (
    PARTITION p202501 VALUES LESS THAN (UNIX_TIMESTAMP('2025-02-01')),
    PARTITION p202502 VALUES LESS THAN (UNIX_TIMESTAMP('2025-03-01')),
    PARTITION p202503 VALUES LESS THAN (UNIX_TIMESTAMP('2025-04-01')),
    PARTITION p202504 VALUES LESS THAN (UNIX_TIMESTAMP('2025-05-01')),
    PARTITION p202505 VALUES LESS THAN (UNIX_TIMESTAMP('2025-06-01')),
    PARTITION p202506 VALUES LESS THAN (UNIX_TIMESTAMP('2025-07-01')),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);
```

---

## 📞 运维支持

### 24/7运维检查清单

#### 日常检查项目
```bash
#!/bin/bash
# daily_check.sh

echo "=== AlingAi Pro 6.0 日常检查报告 ===" 
echo "检查时间: $(date)"
echo

# 1. 系统状态检查
echo "1. 系统状态检查"
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
echo

# 2. 磁盘空间检查
echo "2. 磁盘空间检查"
df -h | grep -v tmpfs
echo

# 3. 内存使用检查
echo "3. 内存使用检查"
free -h
echo

# 4. 数据库状态检查
echo "4. 数据库状态检查"
docker exec alingai-mysql mysqladmin status
echo

# 5. Redis状态检查
echo "5. Redis状态检查"
docker exec alingai-redis redis-cli info server | grep uptime
echo

# 6. 应用健康检查
echo "6. 应用健康检查"
curl -s -o /dev/null -w "HTTP状态码: %{http_code}, 响应时间: %{time_total}s\n" http://localhost/health
echo

# 7. 错误日志检查
echo "7. 最近1小时错误日志"
docker logs alingai-app --since 1h 2>&1 | grep -i error | wc -l
echo

# 8. 安全事件检查
echo "8. 安全事件检查"
tail -n 10 storage/logs/security.log
echo

echo "=== 检查完成 ==="
```

### 告警联系人配置

```yaml
# monitoring/alertmanager.yml
global:
  smtp_smarthost: 'localhost:587'
  smtp_from: 'alerts@alingai.com'

route:
  group_by: ['alertname']
  group_wait: 30s
  group_interval: 5m
  repeat_interval: 12h
  receiver: 'web.hook'
  routes:
  - match:
      severity: critical
    receiver: 'critical-alerts'
  - match:
      severity: warning
    receiver: 'warning-alerts'

receivers:
- name: 'web.hook'
  webhook_configs:
  - url: 'http://webhook.alingai.com/alerts'

- name: 'critical-alerts'
  email_configs:
  - to: 'ops-team@alingai.com'
    subject: '🚨 AlingAi Pro 关键告警'
    body: |
      告警详情:
      {{ range .Alerts }}
      - {{ .Annotations.summary }}
        描述: {{ .Annotations.description }}
      {{ end }}
  slack_configs:
  - api_url: 'YOUR_SLACK_WEBHOOK_URL'
    channel: '#alerts-critical'
    title: 'AlingAi Pro 关键告警'

- name: 'warning-alerts'
  email_configs:
  - to: 'dev-team@alingai.com'
    subject: '⚠️ AlingAi Pro 警告'
```

---

**© 2025 AlingAi Pro 6.0 - 企业级部署和运维指南**
