# PHP扩展安装指南

## gd - 图像处理功能
**Windows:** 取消注释 php.ini 中的 ;extension=gd
**Linux:** sudo apt-get install php-gd (Ubuntu/Debian) 或 yum install php-gd (CentOS)

## redis - 高性能缓存系统
**Windows:** 下载 php_redis.dll 并添加到 php.ini: extension=redis
**Linux:** sudo apt-get install php-redis (Ubuntu/Debian)

## opcache - PHP操作码缓存
**Windows:** 取消注释 php.ini 中的 ;zend_extension=opcache
**Linux:** 通常已包含，检查 php.ini 中的 opcache 设置

