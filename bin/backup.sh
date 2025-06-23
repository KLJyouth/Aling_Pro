#!/bin/bash

# AlingAi Pro 5.0 自动备份脚本

BACKUP_DIR="/backup/alingai-pro"
TIMESTAMP=$(date '+%Y%m%d_%H%M%S')
BACKUP_NAME="alingai_backup_$TIMESTAMP"

mkdir -p $BACKUP_DIR

# 备份数据库
mysqldump -u username -ppassword alingai_pro > $BACKUP_DIR/$BACKUP_NAME.sql

# 备份文件
tar -czf $BACKUP_DIR/$BACKUP_NAME.tar.gz --exclude='storage/logs' --exclude='storage/cache' /var/www/alingai-pro

# 删除7天前的备份
find $BACKUP_DIR -name "alingai_backup_*" -mtime +7 -delete

echo "备份完成: $BACKUP_NAME"
