@echo off
chcp 65001 > nul
cd public
php -S localhost:8000 router.php
