# AlingAi Pro 6.0 PHP 多阶段构建 Dockerfile
# 基于 PHP 8.2 FPM Alpine 版本

# ===============================================
# 基础镜像阶段
# ===============================================
FROM php:8.2-fpm-alpine AS base

# 设置工作目录
WORKDIR /var/www/html

# 安装系统依赖
RUN apk add --no-cache \
    # 基础工具
    curl \
    git \
    unzip \
    zip \
    bash \
    # 图像处理
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    # 数据库
    mysql-client \
    postgresql-dev \
    # 加密
    libsodium-dev \
    # XML/JSON
    libxml2-dev \
    # 压缩
    zlib-dev \
    libzip-dev \
    # 国际化
    icu-dev \
    # 网络
    curl-dev \
    # 其他
    oniguruma-dev \
    autoconf \
    g++ \
    make

# 配置和安装 PHP 扩展
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp && \
    docker-php-ext-install -j$(nproc) \
    # 核心扩展
    pdo \
    pdo_mysql \
    pdo_pgsql \
    mysqli \
    # 图像处理
    gd \
    # 文本处理
    mbstring \
    # 数据格式
    json \
    xml \
    # 压缩
    zip \
    # 加密
    sodium \
    # 国际化
    intl \
    # 数学
    bcmath \
    # 网络
    curl \
    # 进程控制
    pcntl \
    # 系统信息
    posix \
    # 哈希
    hash \
    # 文件信息
    fileinfo \
    # 性能
    opcache

# 安装 Redis 扩展
RUN pecl install redis && docker-php-ext-enable redis

# 安装 MongoDB 扩展
RUN pecl install mongodb && docker-php-ext-enable mongodb

# 安装 Swoole 扩展（用于高性能 WebSocket）
RUN pecl install swoole && docker-php-ext-enable swoole

# 安装 ImageMagick 扩展
RUN apk add --no-cache imagemagick imagemagick-dev && \
    pecl install imagick && \
    docker-php-ext-enable imagick

# 安装 Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 创建 www-data 用户目录
RUN mkdir -p /home/www-data && chown www-data:www-data /home/www-data

# ===============================================
# 开发环境阶段
# ===============================================
FROM base AS development

# 安装 Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# 开发环境 PHP 配置
COPY docker/php/php.dev.ini /usr/local/etc/php/conf.d/99-dev.ini

# 安装开发工具
RUN apk add --no-cache \
    vim \
    nano \
    htop \
    strace

# 设置 Xdebug 配置
RUN echo "xdebug.mode=debug,coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

USER www-data

CMD ["php-fpm"]

# ===============================================
# 生产环境构建阶段
# ===============================================
FROM base AS builder

# 复制应用代码
COPY --chown=www-data:www-data . /var/www/html

# 安装 Composer 依赖（生产环境）
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --optimize-autoloader \
    --no-interaction

# 生成优化的自动加载文件
RUN composer dump-autoload --optimize --classmap-authoritative

# 清理缓存
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# ===============================================
# 生产环境最终阶段
# ===============================================
FROM base AS production

# 生产环境 PHP 配置
COPY docker/php/php.prod.ini /usr/local/etc/php/conf.d/99-prod.ini

# 安全加固：移除不必要的包
RUN apk del autoconf g++ make

# 从构建阶段复制应用文件
COPY --from=builder --chown=www-data:www-data /var/www/html /var/www/html

# 设置正确的权限
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 775 /var/www/html/storage && \
    chmod -R 775 /var/www/html/bootstrap/cache

# 创建必要的目录
RUN mkdir -p \
    /var/www/html/storage/logs \
    /var/www/html/storage/cache \
    /var/www/html/storage/sessions \
    /var/www/html/storage/uploads \
    /var/www/html/storage/temp && \
    chown -R www-data:www-data /var/www/html/storage

# 健康检查脚本
COPY docker/php/healthcheck.php /usr/local/bin/healthcheck.php
RUN chmod +x /usr/local/bin/healthcheck.php

# 设置健康检查
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD php /usr/local/bin/healthcheck.php

# 启动脚本
COPY docker/php/docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# 切换到非特权用户
USER www-data

# 暴露端口
EXPOSE 9000

# 设置入口点
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

# 默认命令
CMD ["php-fpm"]

# ===============================================
# 标签和元数据
# ===============================================
LABEL \
    name="AlingAi Pro 6.0 PHP" \
    version="6.0.0" \
    description="AlingAi Pro 6.0 企业级 PHP 应用容器" \
    maintainer="AlingAi Team" \
    php.version="8.2" \
    environment="production"
