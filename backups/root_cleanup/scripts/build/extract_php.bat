@echo off
echo 解压PHP到portable_php目录...

if not exist portable_php mkdir portable_php
echo 创建便携式PHP目录...

if exist php.zip (
    echo 发现PHP压缩包，开始解压...
    powershell -Command "Expand-Archive -Path php.zip -DestinationPath portable_php -Force"
    
    echo 创建php.ini配置文件...
    echo extension_dir = "ext" > portable_php\php.ini
    echo memory_limit = 1024M >> portable_php\php.ini
    echo max_execution_time = 300 >> portable_php\php.ini
    echo display_errors = On >> portable_php\php.ini
    echo error_reporting = E_ALL >> portable_php\php.ini
    
    if exist portable_php\php.exe (
        echo PHP环境设置成功!
        echo 测试PHP版本:
        portable_php\php.exe -v
    ) else (
        echo 错误: 未找到PHP可执行文件!
    )
) else (
    echo 错误: 未找到php.zip文件!
)

pause