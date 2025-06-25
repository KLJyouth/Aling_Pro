#!/bin/bash

# WebSocket服务器守护进程管理脚本
# 用于在生产环境中管理WebSocket服务器

APP_ROOT="/path/to/alingai_pro"
PID_FILE="$APP_ROOT/storage/websocket.pid"
LOG_FILE="$APP_ROOT/storage/logs/websocket.log"
PHP_BINARY="/usr/bin/php"
SERVER_SCRIPT="$APP_ROOT/bin/websocket-server.php"

# 确保目录存在
mkdir -p "$APP_ROOT/storage/logs"

start_server() {
    if [ -f "$PID_FILE" ]; then
        PID=$(cat "$PID_FILE")
        if ps -p $PID > /dev/null; then
            echo "WebSocket服务器已经在运行 (PID: $PID)"
            return 1
        else
            rm -f "$PID_FILE"
        fi
    fi
    
    echo "正在启动WebSocket服务器..."
    nohup "$PHP_BINARY" "$SERVER_SCRIPT" >> "$LOG_FILE" 2>&1 &
    echo $! > "$PID_FILE"
    
    sleep 2
    
    if ps -p $(cat "$PID_FILE") > /dev/null; then
        echo "WebSocket服务器启动成功 (PID: $(cat "$PID_FILE"))"
        return 0
    else
        echo "WebSocket服务器启动失败"
        rm -f "$PID_FILE"
        return 1
    fi
}

stop_server() {
    if [ ! -f "$PID_FILE" ]; then
        echo "WebSocket服务器未运行"
        return 1
    fi
    
    PID=$(cat "$PID_FILE")
    
    if ! ps -p $PID > /dev/null; then
        echo "WebSocket服务器未运行"
        rm -f "$PID_FILE"
        return 1
    fi
    
    echo "正在停止WebSocket服务器 (PID: $PID)..."
    kill $PID
    
    # 等待进程结束
    for i in {1..10}; do
        if ! ps -p $PID > /dev/null; then
            rm -f "$PID_FILE"
            echo "WebSocket服务器已停止"
            return 0
        fi
        sleep 1
    done
    
    # 强制杀死进程
    echo "强制停止WebSocket服务器..."
    kill -9 $PID
    rm -f "$PID_FILE"
    echo "WebSocket服务器已强制停止"
}

status_server() {
    if [ ! -f "$PID_FILE" ]; then
        echo "WebSocket服务器未运行"
        return 1
    fi
    
    PID=$(cat "$PID_FILE")
    
    if ps -p $PID > /dev/null; then
        echo "WebSocket服务器正在运行 (PID: $PID)"
        return 0
    else
        echo "WebSocket服务器未运行"
        rm -f "$PID_FILE"
        return 1
    fi
}

restart_server() {
    stop_server
    sleep 2
    start_server
}

case "$1" in
    start)
        start_server
        ;;
    stop)
        stop_server
        ;;
    restart)
        restart_server
        ;;
    status)
        status_server
        ;;
    *)
        echo "用法: $0 {start|stop|restart|status}"
        exit 1
        ;;
esac

exit $?
