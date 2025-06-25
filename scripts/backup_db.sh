#!/bin/bash
#
# AlingAi Pro 数据库备份脚本
# 支持MySQL和SQLite数据库备份
# 包含自动压缩和轮换功能
#
# 使用方法:
#   ./backup_db.sh [--mysql|--sqlite] [--config=/path/to/config.conf]
#
# 配置文件格式 (默认为 /etc/alingai/backup.conf):
# BACKUP_DIR="/var/backups/alingai"
# DB_TYPE="mysql"  # mysql 或 sqlite
# MYSQL_HOST="localhost"
# MYSQL_PORT="3306"
# MYSQL_USER="root"
# MYSQL_PASSWORD="your_password"
# MYSQL_DATABASE="alingai_pro"
# SQLITE_DATABASE="/var/www/alingai_pro/database/alingai.sqlite"
# RETENTION_DAYS=30
# EMAIL_NOTIFICATION="admin@example.com"
# COMPRESS="gzip"  # gzip 或 xz
#

# 默认配置
BACKUP_DIR="/var/backups/alingai"
DB_TYPE=""
MYSQL_HOST="localhost"
MYSQL_PORT="3306"
MYSQL_USER="root"
MYSQL_PASSWORD=""
MYSQL_DATABASE="alingai_pro"
SQLITE_DATABASE="/var/www/alingai_pro/database/alingai.sqlite"
RETENTION_DAYS=30
EMAIL_NOTIFICATION=""
COMPRESS="gzip"
CONFIG_FILE="/etc/alingai/backup.conf"
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
LOG_FILE="/var/log/alingai/backup.log"

# 确保日志目录存在
mkdir -p "$(dirname "$LOG_FILE")"
touch "$LOG_FILE"

# 记录日志
log() {
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo "[$timestamp] $1" >> "$LOG_FILE"
    echo "[$timestamp] $1"
}

# 发送通知邮件
send_notification() {
    if [ -n "$EMAIL_NOTIFICATION" ]; then
        local subject="$1"
        local message="$2"
        echo "$message" | mail -s "$subject" "$EMAIL_NOTIFICATION"
    fi
}

# 处理命令行参数
while [[ "$#" -gt 0 ]]; do
    case $1 in
        --mysql) DB_TYPE="mysql"; shift ;;
        --sqlite) DB_TYPE="sqlite"; shift ;;
        --config=*) CONFIG_FILE="${1#*=}"; shift ;;
        *) echo "未知参数: $1"; exit 1 ;;
    esac
done

# 加载配置文件
if [ -f "$CONFIG_FILE" ]; then
    log "加载配置文件: $CONFIG_FILE"
    source "$CONFIG_FILE"
elif [ -f "$SCRIPT_DIR/backup.conf" ]; then
    CONFIG_FILE="$SCRIPT_DIR/backup.conf"
    log "加载本地配置文件: $CONFIG_FILE"
    source "$CONFIG_FILE"
else
    log "警告: 配置文件不存在，使用默认配置"
fi

# 验证必需的配置
if [ -z "$DB_TYPE" ]; then
    log "错误: 未指定数据库类型 (mysql 或 sqlite)"
    exit 1
fi

# 准备备份目录
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_PATH="$BACKUP_DIR/alingai_$TIMESTAMP"
mkdir -p "$BACKUP_DIR"

# 根据压缩类型设置文件扩展名和命令
if [ "$COMPRESS" = "xz" ]; then
    COMPRESS_EXT=".xz"
    COMPRESS_CMD="xz"
    COMPRESS_OPTS="-9"
else
    COMPRESS_EXT=".gz"
    COMPRESS_CMD="gzip"
    COMPRESS_OPTS="-9"
fi

# 备份MySQL数据库
backup_mysql() {
    log "开始备份MySQL数据库: $MYSQL_DATABASE"
    MYSQL_PWD="$MYSQL_PASSWORD" mysqldump -h "$MYSQL_HOST" -P "$MYSQL_PORT" -u "$MYSQL_USER" \
        --single-transaction --quick --lock-tables=false \
        --routines --triggers --events \
        "$MYSQL_DATABASE" > "$BACKUP_PATH.sql" 2>> "$LOG_FILE"
    
    if [ $? -ne 0 ]; then
        log "错误: MySQL备份失败"
        send_notification "AlingAi备份失败" "MySQL数据库备份失败，请检查日志: $LOG_FILE"
        exit 1
    fi
    
    log "MySQL备份成功，开始压缩..."
    $COMPRESS_CMD $COMPRESS_OPTS -f "$BACKUP_PATH.sql"
    
    if [ $? -ne 0 ]; then
        log "警告: 压缩备份文件失败"
    else
        log "压缩完成: $BACKUP_PATH.sql$COMPRESS_EXT"
    fi
}

# 备份SQLite数据库
backup_sqlite() {
    if [ ! -f "$SQLITE_DATABASE" ]; then
        log "错误: SQLite数据库文件不存在: $SQLITE_DATABASE"
        send_notification "AlingAi备份失败" "SQLite数据库文件不存在: $SQLITE_DATABASE"
        exit 1
    fi
    
    log "开始备份SQLite数据库: $SQLITE_DATABASE"
    
    # 使用sqlite3的.backup命令，这是备份过程中最安全的方法
    sqlite3 "$SQLITE_DATABASE" ".backup '$BACKUP_PATH.sqlite'" 2>> "$LOG_FILE"
    
    if [ $? -ne 0 ]; then
        log "错误: SQLite备份失败"
        send_notification "AlingAi备份失败" "SQLite数据库备份失败，请检查日志: $LOG_FILE"
        exit 1
    fi
    
    log "SQLite备份成功，开始压缩..."
    $COMPRESS_CMD $COMPRESS_OPTS -f "$BACKUP_PATH.sqlite"
    
    if [ $? -ne 0 ]; then
        log "警告: 压缩备份文件失败"
    else
        log "压缩完成: $BACKUP_PATH.sqlite$COMPRESS_EXT"
    fi
}

# 执行备份
log "========== 开始备份过程 =========="
log "备份存储目录: $BACKUP_DIR"
log "数据库类型: $DB_TYPE"

case "$DB_TYPE" in
    mysql)
        backup_mysql
        BACKUP_FILE="$BACKUP_PATH.sql$COMPRESS_EXT"
        ;;
    sqlite)
        backup_sqlite
        BACKUP_FILE="$BACKUP_PATH.sqlite$COMPRESS_EXT"
        ;;
    *)
        log "错误: 不支持的数据库类型: $DB_TYPE"
        exit 1
        ;;
esac

# 执行备份轮换 - 删除旧备份
if [ $RETENTION_DAYS -gt 0 ]; then
    log "执行备份轮换 - 删除超过 $RETENTION_DAYS 天的备份"
    find "$BACKUP_DIR" -name "alingai_*" -type f -mtime +$RETENTION_DAYS -delete
fi

# 计算备份大小
BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)

# 完成报告
log "备份完成: $BACKUP_FILE (大小: $BACKUP_SIZE)"
log "========== 备份过程结束 =========="

# 发送成功通知
if [ -n "$EMAIL_NOTIFICATION" ]; then
    send_notification "AlingAi备份成功" "数据库备份已完成\n文件: $BACKUP_FILE\n大小: $BACKUP_SIZE"
fi

exit 0 