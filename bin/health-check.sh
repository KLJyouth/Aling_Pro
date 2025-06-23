#!/bin/bash

# AlingAi Pro 5.0 健康检查脚本

LOG_FILE="/var/log/alingai-health.log"
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')

echo "[$TIMESTAMP] 开始健康检查" >> $LOG_FILE

# 检查Web服务
if curl -f -s -o /dev/null http://localhost/health; then
    echo "[$TIMESTAMP] Web服务: 正常" >> $LOG_FILE
else
    echo "[$TIMESTAMP] Web服务: 异常" >> $LOG_FILE
    # 发送警报
    echo "Web服务异常" | mail -s "AlingAi Pro 警报" admin@your-domain.com
fi

# 检查数据库
if php -r "try { new PDO('mysql:host=localhost;dbname=alingai_pro', 'username', 'password'); echo 'OK'; } catch(Exception $e) { echo 'FAIL'; }"; then
    echo "[$TIMESTAMP] 数据库: 正常" >> $LOG_FILE
else
    echo "[$TIMESTAMP] 数据库: 异常" >> $LOG_FILE
fi

# 检查磁盘空间
DISK_USAGE=$(df -h / | awk 'NR==2{print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 80 ]; then
    echo "[$TIMESTAMP] 磁盘使用率: ${DISK_USAGE}% (警告)" >> $LOG_FILE
else
    echo "[$TIMESTAMP] 磁盘使用率: ${DISK_USAGE}% (正常)" >> $LOG_FILE
fi

echo "[$TIMESTAMP] 健康检查完成" >> $LOG_FILE
