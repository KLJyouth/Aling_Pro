@echo off
echo ===== AlingAi Pro ESLint和错误修复工具 =====
echo 时间: %date% %time%
echo.

set PROJECT_ROOT=%~dp0

echo 检查Node.js是否可用...
node --version

if %ERRORLEVEL% neq 0 (
    echo 系统中未找到Node.js！
    echo 请安装Node.js然后重试。
    echo 下载地址: https://nodejs.org/
    pause
    exit /b 1
)

echo 检查ESLint依赖...
if not exist "%PROJECT_ROOT%\node_modules\.bin\eslint.cmd" (
    echo 未找到ESLint，正在安装所需依赖...
    
    echo 创建package.json（如果不存在）...
    if not exist "%PROJECT_ROOT%\package.json" (
        echo {^
          "name": "alingai-pro-lint-tools",^
          "version": "1.0.0",^
          "private": true,^
          "scripts": {^
            "lint": "eslint .",^
            "lint:fix": "eslint . --fix"^
          }^
        } > "%PROJECT_ROOT%\package.json"
    )
    
    echo 安装ESLint和相关插件...
    cd "%PROJECT_ROOT%"
    npm install --save-dev eslint@9.x @eslint/js typescript-eslint eslint-plugin-vue eslint-plugin-import eslint-plugin-security eslint-plugin-sonarjs eslint-config-prettier
)

echo 验证ESLint安装...
npx eslint --version

if %ERRORLEVEL% neq 0 (
    echo ESLint安装有问题，请检查错误信息。
    pause
    exit /b 1
)

echo.
echo ESLint配置正常，开始执行检查和修复...

echo.
echo 1. 执行ESLint检查（不修复）...
npx eslint . --ext .js,.ts,.vue

echo.
echo 2. 执行ESLint自动修复...
npx eslint . --ext .js,.ts,.vue --fix

echo.
echo ESLint检查和修复完成！
echo 如果上方有显示错误，可能需要手动修复一些问题。
pause 