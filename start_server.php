<?php
/**
 * AlingAi Pro 启动脚本
 * 使用PHP内置服务器启动系统
 * 
 * @version 1.0.0
 * @author AlingAi Team
 */

echo "正在启动 AlingAi Pro 系统...\n";
echo "--------------------------------------\n";
echo "访问地址: http://localhost:8000\n";
echo "管理面板: http://localhost:8000/admin/\n";
echo "API文档: http://localhost:8000/api-docs.html\n";
echo "--------------------------------------\n";
echo "按 Ctrl+C 停止服务器\n\n";

// 启动内置服务器
passthru('php -S localhost:8000 -t public');
