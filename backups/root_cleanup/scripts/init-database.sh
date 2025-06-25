#!/bin/bash
# MySQL数据库初始化脚本
# 用于测试环境的简单数据库设置

# 检查MySQL是否运行
if ! command -v mysql &> /dev/null; then
    echo "MySQL未安装，请先安装MySQL 5.7.43+"
    exit 1
fi

# 创建数据库
echo "正在创建数据库..."
mysql -u root -p -e "
CREATE DATABASE IF NOT EXISTS alingai_pro;
CREATE USER IF NOT EXISTS 'alingai'@'localhost' IDENTIFIED BY 'alingai123';
GRANT ALL PRIVILEGES ON alingai_pro.* TO 'alingai'@'localhost';
FLUSH PRIVILEGES;
"

# 创建基本表结构
echo "正在创建表结构..."
mysql -u alingai -palingai123 alingai_pro < database/migrations/001_create_users_table.sql
mysql -u alingai -palingai123 alingai_pro < database/migrations/002_create_chat_sessions_table.sql
mysql -u alingai -palingai123 alingai_pro < database/migrations/003_create_chat_messages_table.sql

echo "数据库初始化完成！"
echo "数据库名: alingai_pro"
echo "用户名: alingai"
echo "密码: alingai123"
