<?php
/**
 * AlingAi Pro Azure部署包准备脚本
 * 根据Azure最佳实践准备生产环境部署包
 */

echo "🚀 AlingAi Pro Azure部署包准备\n";
echo "======================================\n";

// 部署配置
$deployConfig = [
    'app_name' => 'AlingAi Pro',
    'version' => '2.0.0',
    'environment' => 'production',
    'php_version' => '8.1',
    'deployment_path' => __DIR__ . '/deployment',
    'zip_file' => __DIR__ . '/deployment.zip'
];

echo "应用: {$deployConfig['app_name']} v{$deployConfig['version']}\n";
echo "环境: {$deployConfig['environment']}\n";
echo "PHP版本: {$deployConfig['php_version']}\n\n";

// 1. 创建部署目录
function createDeploymentDirectory($config) {
    echo "📁 创建部署目录...\n";
    
    $deployPath = $config['deployment_path'];
      // 清理旧的部署目录
    if (is_dir($deployPath)) {
        echo "  清理旧的部署目录...\n";
        // Windows兼容的目录删除
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec("rmdir /s /q \"{$deployPath}\"");
        } else {
            exec("rm -rf {$deployPath}");
        }
    }
    
    // 创建新的部署目录
    mkdir($deployPath, 0755, true);
    
    echo "  ✓ 部署目录创建完成: {$deployPath}\n\n";
    return $deployPath;
}

// 2. 复制应用文件
function copyApplicationFiles($deployPath) {
    echo "📦 复制应用文件...\n";
    
    // 需要包含的目录和文件
    $includeItems = [
        'public',
        'src',
        'config',
        'resources',
        'storage',
        'vendor',
        'composer.json',
        'composer.lock',
        'router.php',
        'start_system.php',
        'database_management.php',
        'websocket_server.php',
        'websocket_manager.php'
    ];
      foreach ($includeItems as $item) {
        if (file_exists($item)) {
            if (is_dir($item)) {
                echo "  复制目录: {$item}/\n";
                // Windows兼容的目录复制
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    exec("xcopy \"{$item}\" \"{$deployPath}\\{$item}\" /E /I /Y");
                } else {
                    exec("cp -r {$item} {$deployPath}/");
                }
            } else {
                echo "  复制文件: {$item}\n";
                copy($item, "{$deployPath}/{$item}");
            }
        } else {
            echo "  ⚠️  跳过不存在的项目: {$item}\n";
        }
    }
    
    echo "  ✓ 应用文件复制完成\n\n";
}

// 3. 生成生产环境配置
function generateProductionConfig($deployPath) {
    echo "⚙️  生成生产环境配置...\n";
    
    // 生产环境 .env 模板
    $envProduction = <<<ENV
# AlingAi Pro 生产环境配置
APP_NAME="AlingAi Pro"
APP_ENV=production
APP_DEBUG=false
APP_KEY=\${APP_KEY}
APP_URL=\${APP_URL}

# 数据库配置 (Azure MySQL)
DB_CONNECTION=mysql
DB_HOST=\${DB_HOST}
DB_PORT=3306
DB_DATABASE=alingai
DB_USERNAME=\${DB_USERNAME}
DB_PASSWORD=\${DB_PASSWORD}

# AI服务配置
DEEPSEEK_API_KEY=\${DEEPSEEK_API_KEY}
DEEPSEEK_API_URL=https://api.deepseek.com

# 邮件配置
MAIL_MAILER=smtp
MAIL_HOST=smtp.exmail.qq.com
MAIL_PORT=465
MAIL_USERNAME=admin@gxggm.com
MAIL_PASSWORD=\${MAIL_PASSWORD}
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=admin@gxggm.com
MAIL_FROM_NAME="AlingAi Pro"

# Azure存储配置
AZURE_STORAGE_ACCOUNT=\${AZURE_STORAGE_ACCOUNT}
AZURE_STORAGE_KEY=\${AZURE_STORAGE_KEY}
AZURE_STORAGE_CONTAINER=uploads

# 缓存配置
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

# 应用洞察
APPINSIGHTS_INSTRUMENTATIONKEY=\${APPINSIGHTS_INSTRUMENTATIONKEY}

ENV;
    
    file_put_contents("{$deployPath}/.env.production", $envProduction);
    
    // web.config for Azure App Service
    $webConfig = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<configuration>
  <system.webServer>
    <urlRewrite>
      <rules>
        <rule name="Imported Rule 1" stopProcessing="true">
          <match url="^(.*)/$" ignoreCase="false" />
          <conditions>
            <add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />
          </conditions>
          <action type="Redirect" redirectType="Permanent" url="/{R:1}" />
        </rule>
        <rule name="Imported Rule 2" stopProcessing="true">
          <match url="^" ignoreCase="false" />
          <conditions>
            <add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />
            <add input="{REQUEST_FILENAME}" matchType="IsFile" ignoreCase="false" negate="true" />
          </conditions>
          <action type="Rewrite" url="router.php" />
        </rule>
      </rules>
    </urlRewrite>
    <defaultDocument>
      <files>
        <clear />
        <add value="public/index.html" />
        <add value="router.php" />
      </files>
    </defaultDocument>
    <httpErrors errorMode="DetailedLocalOnly" />
  </system.webServer>
</configuration>
XML;
    
    file_put_contents("{$deployPath}/web.config", $webConfig);
    
    // composer.json 生产环境优化
    $composerJson = json_decode(file_get_contents('composer.json'), true);
    $composerJson['config']['optimize-autoloader'] = true;
    $composerJson['config']['classmap-authoritative'] = true;
    $composerJson['config']['apcu-autoloader'] = true;
    
    file_put_contents("{$deployPath}/composer.json", json_encode($composerJson, JSON_PRETTY_PRINT));
    
    echo "  ✓ 生产环境配置生成完成\n\n";
}

// 4. 优化部署包
function optimizeDeployment($deployPath) {
    echo "🔧 优化部署包...\n";
    
    // 删除开发环境文件
    $removeItems = [
        'tests',
        'docs',
        '.git',
        '.env',
        '.env.example',
        'debug_*.php',
        'test_*.php',
        'phpunit.xml',
        'README.md',
        'deployment_readiness.php',
        'performance_test.php',
        'integration_test.php'
    ];
    
    foreach ($removeItems as $item) {
        $fullPath = "{$deployPath}/{$item}";        if (file_exists($fullPath)) {
            echo "  删除: {$item}\n";
            if (is_dir($fullPath)) {
                // Windows兼容的目录删除
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    exec("rmdir /s /q \"{$fullPath}\"");
                } else {
                    exec("rm -rf {$fullPath}");
                }
            } else {
                unlink($fullPath);
            }
        }
    }
    
    // 创建必要的目录结构
    $requiredDirs = [
        'storage/logs',
        'storage/cache',
        'storage/uploads',
        'storage/data'
    ];
    
    foreach ($requiredDirs as $dir) {
        $fullPath = "{$deployPath}/{$dir}";
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
            echo "  创建目录: {$dir}\n";
        }
        
        // 添加 .gitkeep 文件
        touch("{$fullPath}/.gitkeep");
    }
    
    echo "  ✓ 部署包优化完成\n\n";
}

// 5. 创建部署ZIP包
function createDeploymentZip($config) {
    echo "📦 创建部署ZIP包...\n";
    
    $deployPath = $config['deployment_path'];
    $zipFile = $config['zip_file'];
    
    // 删除旧的ZIP文件
    if (file_exists($zipFile)) {
        unlink($zipFile);
    }
    
    // 创建ZIP包
    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
        
        // 递归添加文件到ZIP
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($deployPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relativePath = str_replace($deployPath . '/', '', $file->getPathname());
                $zip->addFile($file->getPathname(), $relativePath);
            }
        }
        
        $zip->close();
        
        $fileSize = round(filesize($zipFile) / 1024 / 1024, 2);
        echo "  ✓ 部署ZIP包创建完成\n";
        echo "  文件: {$zipFile}\n";
        echo "  大小: {$fileSize} MB\n\n";
        
    } else {
        echo "  ❌ 无法创建ZIP文件\n";
        exit(1);
    }
}

// 6. 生成部署文档
function generateDeploymentDocs($config) {
    echo "📝 生成部署文档...\n";
    
    $docs = <<<DOCS
# AlingAi Pro Azure部署指南

## 部署信息
- 应用名称: {$config['app_name']}
- 版本: {$config['version']}
- 环境: {$config['environment']}
- PHP版本: {$config['php_version']}
- 构建时间: {$config['build_time']}

## 部署步骤

### 1. 准备Azure资源
```bash
# 使用Bicep模板部署基础设施
az deployment group create \\
  --resource-group rg-alingai-pro-prod \\
  --template-file infra/main.bicep \\
  --parameters infra/main.prod.parameters.json
```

### 2. 部署应用代码
```bash
# 使用Azure CLI部署
az webapp deployment source config-zip \\
  --name alingai-pro-prod-webapp \\
  --resource-group rg-alingai-pro-prod \\
  --src deployment.zip
```

### 3. 配置环境变量
在Azure Portal中配置以下环境变量：
- APP_KEY: 应用密钥
- DB_PASSWORD: 数据库密码
- DEEPSEEK_API_KEY: AI服务密钥
- MAIL_PASSWORD: 邮件服务密码

### 4. 执行数据库迁移
```bash
# 通过SSH连接执行
az webapp ssh --name alingai-pro-prod-webapp --resource-group rg-alingai-pro-prod
cd /home/site/wwwroot
php database_management.php migrate
```

### 5. 验证部署
访问应用URL验证部署成功：
- 主页: https://your-app.azurewebsites.net
- 健康检查: https://your-app.azurewebsites.net/api/system/status

## 监控和维护

### Application Insights
- 应用性能监控已自动配置
- 查看仪表板: Azure Portal > Application Insights

### 日志查看
```bash
# 查看应用日志
az webapp log tail --name alingai-pro-prod-webapp --resource-group rg-alingai-pro-prod
```

### 备份和恢复
- 数据库自动备份已配置（7天保留）
- 应用文件需要手动备份

## 故障排除

### 常见问题
1. 数据库连接失败：检查防火墙规则和连接字符串
2. 文件上传失败：检查存储权限和配置
3. 邮件发送失败：验证SMTP配置

### 支持联系
- 技术支持: admin@gxggm.com
- 文档更新: {$config['build_time']}

DOCS;
    
    file_put_contents('deployment-guide.md', $docs);
    echo "  ✓ 部署文档生成完成: deployment-guide.md\n\n";
}

// 主执行流程
function main() {
    global $deployConfig;
    
    $deployConfig['build_time'] = date('Y-m-d H:i:s');
    
    echo "开始时间: {$deployConfig['build_time']}\n\n";
    
    // 执行部署准备步骤
    $deployPath = createDeploymentDirectory($deployConfig);
    copyApplicationFiles($deployPath);
    generateProductionConfig($deployPath);
    optimizeDeployment($deployPath);
    createDeploymentZip($deployConfig);
    generateDeploymentDocs($deployConfig);
    
    echo "🎉 Azure部署包准备完成！\n";
    echo "======================================\n";
    echo "部署文件: {$deployConfig['zip_file']}\n";
    echo "部署文档: deployment-guide.md\n";
    echo "下一步: 运行 ./deploy-azure.sh 开始部署\n\n";
}

// 执行主流程
try {
    main();
} catch (Exception $e) {
    echo "❌ 部署准备失败: " . $e->getMessage() . "\n";
    exit(1);
}
?>
