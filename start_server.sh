#!/bin/bash

echo -e "\033[0;32m正在启动 AlingAi Pro 系统...\033[0m"
echo -e "\033[0;32m--------------------------------------\033[0m"
echo -e "\033[0;33m访问地址: http://localhost:8000\033[0m"
echo -e "\033[0;33m管理面板: http://localhost:8000/admin/\033[0m"
echo -e "\033[0;33mAPI文档: http://localhost:8000/api-docs.html\033[0m"
echo -e "\033[0;32m--------------------------------------\033[0m"
echo -e "\033[0;31m按 Ctrl+C 停止服务器\033[0m\n"

# 切换到public目录并启动PHP内置服务器
cd public && php -S localhost:8000 router.php 