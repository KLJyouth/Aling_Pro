@echo off
echo === AlingAi_pro Admin IT Center Initialization Script ===
echo.

REM Create directory structure
echo Creating directory structure...
mkdir admin-center 2>nul
mkdir admin-center\app 2>nul
mkdir admin-center\app\Controllers 2>nul
mkdir admin-center\app\Models 2>nul
mkdir admin-center\app\Services 2>nul
mkdir admin-center\config 2>nul
mkdir admin-center\public 2>nul
mkdir admin-center\resources 2>nul
mkdir admin-center\resources\js 2>nul
mkdir admin-center\resources\css 2>nul
mkdir admin-center\resources\views 2>nul
mkdir admin-center\routes 2>nul
mkdir admin-center\database 2>nul
mkdir admin-center\database\migrations 2>nul
mkdir admin-center\tests 2>nul
mkdir admin-center\tools 2>nul
mkdir admin-center\docs 2>nul

REM Create base files
echo Creating base files...
echo # AlingAi_pro Admin IT Center > admin-center\README.md
echo { "name": "alingai/admin-center", "description": "AlingAi_pro Admin IT Center", "type": "project", "require": { "php": "^8.1" } } > admin-center\composer.json
echo ^<?php > admin-center\index.php
echo // Admin IT Center Entry File >> admin-center\index.php
echo // This is a placeholder file, will be replaced with actual Laravel entry file >> admin-center\index.php

echo.
echo Directory structure created!
echo.
echo Next steps:
echo 1. Install PHP 8.1+ and Composer
echo 2. Install Laravel framework
echo 3. Configure database connection
echo 4. Start developing the Admin IT Center
pause 