#!/bin/bash
#
# AlingAi Pro 服务控制脚本
# 用于Linux服务器自动启动和关闭AlingAi Pro服务
#
# 使用方法:
#   sudo ./alingai_service.sh {start|stop|restart|status}
#
# 安装为系统服务:
#   sudo cp alingai_service.sh /etc/init.d/alingai
#   sudo chmod +x /etc/init.d/alingai
#   sudo update-rc.d alingai defaults
#
# 系统服务操作:
#   sudo service alingai start
#   sudo service alingai stop
#   sudo service alingai restart
#   sudo service alingai status
#

### BEGIN INIT INFO
# Provides:          alingai
# Required-Start:    $remote_fs $network $named $syslog
# Required-Stop:     $remote_fs $network $named $syslog
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: AlingAi Pro 服务
# Description:       管理AlingAi Pro系统的PHP服务和相关进程
### END INIT INFO

# 配置变量
NAME="AlingAi Pro"
SCRIPT_NAME=$(basename "$0")
ALINGAI_ROOT="/var/www/alingai_pro"  # 安装目录，请根据实际安装路径修改
ALINGAI_USER="www-data"              # 运行服务的用户，请根据实际情况修改
ALINGAI_GROUP="www-data"             # 运行服务的用户组
PID_DIR="/var/run/alingai"           # PID文件目录
LOG_DIR="/var/log/alingai"           # 日志目录
PHP_BIN="/usr/bin/php"               # PHP执行文件路径
WORKERS=4                            # 工作进程数
TIMEOUT=30                           # 超时时间

# 确保存在所需目录
create_directories() {
    if [ ! -d "$PID_DIR" ]; then
        mkdir -p "$PID_DIR"
        chown $ALINGAI_USER:$ALINGAI_GROUP "$PID_DIR"
    fi
    
    if [ ! -d "$LOG_DIR" ]; then
        mkdir -p "$LOG_DIR"
        chown $ALINGAI_USER:$ALINGAI_GROUP "$LOG_DIR"
    fi
}

# 检查是否以root用户运行
if [ "$(id -u)" != "0" ]; then
   echo "错误: 此脚本必须以root用户运行" 1>&2
   exit 1
fi

# 检查服务状态
check_status() {
    local service_name=$1
    local pid_file="$PID_DIR/$service_name.pid"
    
    if [ -f "$pid_file" ]; then
        local pid=$(cat "$pid_file")
        if ps -p "$pid" > /dev/null; then
            echo "$service_name 正在运行 (PID: $pid)"
            return 0
        else
            echo "$service_name 已停止 (残留PID文件)"
            return 1
        fi
    else
        echo "$service_name 未运行"
        return 2
    fi
}

# 启动队列处理器
start_queue_worker() {
    echo "启动队列处理器..."
    cd "$ALINGAI_ROOT"
    
    for (( i=1; i<=$WORKERS; i++ )); do
        local pid_file="$PID_DIR/queue_worker_$i.pid"
        local log_file="$LOG_DIR/queue_worker_$i.log"
        
        # 检查是否已经运行
        if [ -f "$pid_file" ]; then
            local pid=$(cat "$pid_file")
            if ps -p "$pid" > /dev/null; then
                echo "队列处理器 #$i 已经在运行 (PID: $pid)"
                continue
            fi
        fi
        
        echo "启动队列处理器 #$i..."
        nohup sudo -u $ALINGAI_USER $PHP_BIN $ALINGAI_ROOT/scripts/artisan queue:work --tries=3 --timeout=$TIMEOUT > "$log_file" 2>&1 &
        echo $! > "$pid_file"
        
        # 检查启动是否成功
        sleep 1
        if ps -p $! > /dev/null; then
            echo "队列处理器 #$i 启动成功 (PID: $!)"
        else
            echo "队列处理器 #$i 启动失败"
        fi
    done
}

# 启动计划任务处理器
start_scheduler() {
    echo "启动计划任务处理器..."
    local pid_file="$PID_DIR/scheduler.pid"
    local log_file="$LOG_DIR/scheduler.log"
    
    # 检查是否已经运行
    if [ -f "$pid_file" ]; then
        local pid=$(cat "$pid_file")
        if ps -p "$pid" > /dev/null; then
            echo "计划任务处理器已经在运行 (PID: $pid)"
            return
        fi
    fi
    
    cd "$ALINGAI_ROOT"
    nohup sudo -u $ALINGAI_USER $PHP_BIN $ALINGAI_ROOT/scripts/artisan schedule:run > "$log_file" 2>&1 &
    echo $! > "$pid_file"
    
    # 创建计划任务
    # 确保系统上每分钟执行一次计划任务处理
    crontab_file=$(mktemp)
    crontab -l -u $ALINGAI_USER > "$crontab_file" 2>/dev/null || true
    
    # 检查是否已存在相同的定时任务
    if ! grep -q "$ALINGAI_ROOT/scripts/artisan schedule:run" "$crontab_file"; then
        echo "* * * * * cd $ALINGAI_ROOT && $PHP_BIN scripts/artisan schedule:run >> $LOG_DIR/scheduler.log 2>&1" >> "$crontab_file"
        crontab -u $ALINGAI_USER "$crontab_file"
        echo "已添加计划任务到crontab"
    else
        echo "计划任务已存在于crontab中"
    fi
    
    rm -f "$crontab_file"
}

# 启动监控服务
start_monitor() {
    echo "启动系统监控服务..."
    local pid_file="$PID_DIR/monitor.pid"
    local log_file="$LOG_DIR/monitor.log"
    
    # 检查是否已经运行
    if [ -f "$pid_file" ]; then
        local pid=$(cat "$pid_file")
        if ps -p "$pid" > /dev/null; then
            echo "监控服务已经在运行 (PID: $pid)"
            return
        fi
    fi
    
    cd "$ALINGAI_ROOT"
    nohup sudo -u $ALINGAI_USER $PHP_BIN $ALINGAI_ROOT/scripts/artisan monitor:run > "$log_file" 2>&1 &
    echo $! > "$pid_file"
    
    # 检查启动是否成功
    sleep 1
    if ps -p $! > /dev/null; then
        echo "监控服务启动成功 (PID: $!)"
    else
        echo "监控服务启动失败"
    fi
}

# 停止服务
stop_service() {
    local service_name=$1
    local pid_file="$PID_DIR/$service_name.pid"
    
    if [ -f "$pid_file" ]; then
        local pid=$(cat "$pid_file")
        echo "正在停止 $service_name (PID: $pid)..."
        
        # 尝试优雅关闭
        if ps -p "$pid" > /dev/null; then
            kill -15 "$pid"
            sleep 2
        fi
        
        # 如果进程仍在运行，强制终止
        if ps -p "$pid" > /dev/null; then
            echo "强制终止 $service_name (PID: $pid)..."
            kill -9 "$pid"
            sleep 1
        fi
        
        # 删除PID文件
        rm -f "$pid_file"
        echo "$service_name 已停止"
    else
        echo "$service_name 未运行"
    fi
}

# 根据传入的命令执行相应操作
case "$1" in
    start)
        echo "启动 $NAME 服务..."
        create_directories
        start_queue_worker
        start_scheduler
        start_monitor
        echo "$NAME 服务已启动"
        ;;
    
    stop)
        echo "停止 $NAME 服务..."
        # 停止队列处理器
        for (( i=1; i<=$WORKERS; i++ )); do
            stop_service "queue_worker_$i"
        done
        
        # 停止其他服务
        stop_service "scheduler"
        stop_service "monitor"
        
        echo "$NAME 服务已停止"
        ;;
    
    restart)
        $0 stop
        sleep 2
        $0 start
        ;;
    
    status)
        echo "$NAME 服务状态:"
        
        # 检查队列处理器状态
        for (( i=1; i<=$WORKERS; i++ )); do
            check_status "queue_worker_$i"
        done
        
        # 检查其他服务状态
        check_status "scheduler"
        check_status "monitor"
        ;;
    
    *)
        echo "用法: $SCRIPT_NAME {start|stop|restart|status}" >&2
        exit 1
        ;;
esac

exit 0 