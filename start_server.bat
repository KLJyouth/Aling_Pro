@echo off
echo 正在启动 AlingAi Pro 系统...
echo --------------------------------------
echo 访问地址: http://localhost:8000
echo 管理面板: http://localhost:8000/admin/
echo API文档: http://localhost:8000/api-docs.html
echo --------------------------------------
echo 按 Ctrl+C 停止服务器

cd public
php -S localhost:8000 router.php 