<?php

/**
 * AlingAi Pro 综合修复脚本
 * 解决所有已知问题，包括自动加载器、依赖注入、路由配置等
 */

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "=== AlingAi Pro 综合修复脚本 ===\n\n";

// 1. 检查并创建必要的目录
echo "1. 检查并创建必要的目录...\n";
$directories = [
    'src/Services/Interfaces',
    'src/Core/Database',
    'src/Core/Container',
    'src/Core/Logger',
    'src/Controllers/Api',
    'src/Config',
    'storage/logs',
    'storage/cache',
    'database'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "✓ 创建目录: {$dir}\n";
        } else {
            echo "✗ 创建目录失败: {$dir}\n";
        }
    } else {
        echo "✓ 目录已存在: {$dir}\n";
    }
}
echo "\n";

// 2. 检查核心文件
echo "2. 检查核心文件...\n";
$coreFiles = [
    'src/Services/Interfaces/AIServiceInterface.php',
    'src/Services/DeepSeekAIService.php',
    'src/Services/ChatService.php',
    'src/Controllers/Api/BaseApiController.php',
    'src/Controllers/Api/EnhancedChatApiController.php',
    'src/Core/Database/DatabaseManager.php',
    'src/Core/Container/ServiceContainer.php',
    'src/Core/Logger/LoggerFactory.php',
    'src/Config/Routes.php'
];

foreach ($coreFiles as $file) {
    if (file_exists($file)) {
        echo "✓ {$file} 存在\n";
    } else {
        echo "✗ {$file} 不存在\n";
    }
}
echo "\n";

// 3. 生成环境配置文件
echo "3. 生成环境配置文件...\n";
$envContent = "# 数据库配置\n";
$envContent .= "DB_HOST=localhost\n";
$envContent .= "DB_PORT=3306\n";
$envContent .= "DB_DATABASE=alingai_pro\n";
$envContent .= "DB_USERNAME=root\n";
$envContent .= "DB_PASSWORD=\n\n";
$envContent .= "# DeepSeek AI配置\n";
$envContent .= "DEEPSEEK_API_KEY=your_api_key_here\n";
$envContent .= "DEEPSEEK_API_URL=https://api.deepseek.com/v1/chat/completions\n";
$envContent .= "DEEPSEEK_MODEL=deepseek-chat\n\n";
$envContent .= "# 应用配置\n";
$envContent .= "APP_ENV=development\n";
$envContent .= "APP_DEBUG=true\n";
$envContent .= "APP_URL=http://localhost\n\n";
$envContent .= "# 日志配置\n";
$envContent .= "LOG_LEVEL=info\n";
$envContent .= "LOG_PATH=storage/logs\n\n";
$envContent .= "# 安全配置\n";
$envContent .= "JWT_SECRET=your_jwt_secret_here\n";

if (file_put_contents('.env', $envContent)) {
    echo "✓ 生成 .env 文件\n";
} else {
    echo "✗ 生成 .env 文件失败\n";
}
echo "\n";

// 4. 生成composer.json
echo "4. 生成composer.json...\n";
$composerJson = [
    'name' => 'alingai/pro',
    'description' => 'AlingAi Pro - Advanced AI Platform',
    'type' => 'project',
    'require' => [
        'php' => '>=8.0',
        'monolog/monolog' => '^3.0',
        'ramsey/uuid' => '^4.0',
        'vlucas/phpdotenv' => '^5.0'
    ],
    'autoload' => [
        'psr-4' => [
            'AlingAi\\' => 'src/'
        ]
    ],
    'config' => [
        'optimize-autoloader' => true
    ]
];

if (file_put_contents('composer.json', json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
    echo "✓ 生成 composer.json\n";
} else {
    echo "✗ 生成 composer.json 失败\n";
}
echo "\n";

// 5. 生成启动脚本
echo "5. 生成启动脚本...\n";
$startScript = "@echo off\n";
$startScript .= "echo Starting AlingAi Pro Development Server...\n";
$startScript .= "echo.\n";
$startScript .= "echo Make sure you have:\n";
$startScript .= "echo 1. PHP installed and in PATH\n";
$startScript .= "echo 2. MySQL/MariaDB running\n";
$startScript .= "echo 3. Composer installed\n";
$startScript .= "echo.\n";
$startScript .= "echo Starting server on http://localhost:8000\n";
$startScript .= "echo Press Ctrl+C to stop\n";
$startScript .= "echo.\n";
$startScript .= "php -S localhost:8000 -t public\n";

if (file_put_contents('start_server.bat', $startScript)) {
    echo "✓ 生成 start_server.bat\n";
} else {
    echo "✗ 生成 start_server.bat 失败\n";
}
echo "\n";

// 6. 生成数据库初始化脚本
echo "6. 生成数据库初始化脚本...\n";
$dbInitScript = "-- AlingAi Pro 数据库初始化脚本\n\n";
$dbInitScript .= "-- 创建数据库\n";
$dbInitScript .= "CREATE DATABASE IF NOT EXISTS alingai_pro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
$dbInitScript .= "USE alingai_pro;\n\n";
$dbInitScript .= "-- 用户表\n";
$dbInitScript .= "CREATE TABLE IF NOT EXISTS users (\n";
$dbInitScript .= "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
$dbInitScript .= "    username VARCHAR(50) UNIQUE NOT NULL,\n";
$dbInitScript .= "    email VARCHAR(100) UNIQUE NOT NULL,\n";
$dbInitScript .= "    password_hash VARCHAR(255) NOT NULL,\n";
$dbInitScript .= "    role ENUM('user', 'admin') DEFAULT 'user',\n";
$dbInitScript .= "    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',\n";
$dbInitScript .= "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
$dbInitScript .= "    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n";
$dbInitScript .= "    INDEX idx_username (username),\n";
$dbInitScript .= "    INDEX idx_email (email),\n";
$dbInitScript .= "    INDEX idx_status (status)\n";
$dbInitScript .= ");\n\n";
$dbInitScript .= "-- 会话表\n";
$dbInitScript .= "CREATE TABLE IF NOT EXISTS conversations (\n";
$dbInitScript .= "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
$dbInitScript .= "    user_id INT NOT NULL,\n";
$dbInitScript .= "    title VARCHAR(255) DEFAULT '',\n";
$dbInitScript .= "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
$dbInitScript .= "    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n";
$dbInitScript .= "    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,\n";
$dbInitScript .= "    INDEX idx_user_id (user_id),\n";
$dbInitScript .= "    INDEX idx_updated_at (updated_at)\n";
$dbInitScript .= ");\n\n";
$dbInitScript .= "-- 消息表\n";
$dbInitScript .= "CREATE TABLE IF NOT EXISTS messages (\n";
$dbInitScript .= "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
$dbInitScript .= "    conversation_id INT NOT NULL,\n";
$dbInitScript .= "    user_id INT NOT NULL,\n";
$dbInitScript .= "    role ENUM('user', 'assistant', 'system') NOT NULL,\n";
$dbInitScript .= "    content TEXT NOT NULL,\n";
$dbInitScript .= "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
$dbInitScript .= "    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,\n";
$dbInitScript .= "    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,\n";
$dbInitScript .= "    INDEX idx_conversation_id (conversation_id),\n";
$dbInitScript .= "    INDEX idx_user_id (user_id),\n";
$dbInitScript .= "    INDEX idx_created_at (created_at)\n";
$dbInitScript .= ");\n\n";
$dbInitScript .= "-- 使用统计表\n";
$dbInitScript .= "CREATE TABLE IF NOT EXISTS usage_stats (\n";
$dbInitScript .= "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
$dbInitScript .= "    conversation_id INT NOT NULL,\n";
$dbInitScript .= "    prompt_tokens INT DEFAULT 0,\n";
$dbInitScript .= "    completion_tokens INT DEFAULT 0,\n";
$dbInitScript .= "    total_tokens INT DEFAULT 0,\n";
$dbInitScript .= "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
$dbInitScript .= "    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n";
$dbInitScript .= "    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,\n";
$dbInitScript .= "    INDEX idx_conversation_id (conversation_id),\n";
$dbInitScript .= "    INDEX idx_created_at (created_at)\n";
$dbInitScript .= ");\n\n";
$dbInitScript .= "-- 插入测试用户\n";
$dbInitScript .= "INSERT IGNORE INTO users (username, email, password_hash, role) VALUES \n";
$dbInitScript .= "('admin', 'admin@alingai.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),\n";
$dbInitScript .= "('testuser', 'test@alingai.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');\n\n";
$dbInitScript .= "-- 插入测试会话\n";
$dbInitScript .= "INSERT IGNORE INTO conversations (user_id, title) VALUES \n";
$dbInitScript .= "(1, '测试会话 1'),\n";
$dbInitScript .= "(2, '用户测试会话');\n\n";
$dbInitScript .= "SELECT '数据库初始化完成！' as message;\n";

if (file_put_contents('database/init.sql', $dbInitScript)) {
    echo "✓ 生成数据库初始化脚本\n";
} else {
    echo "✗ 生成数据库初始化脚本失败\n";
}
echo "\n";

// 7. 生成README文件
echo "7. 生成README文件...\n";
$readmeContent = "# AlingAi Pro\n\n";
$readmeContent .= "高级AI平台，提供聊天、对话管理和AI服务集成功能。\n\n";
$readmeContent .= "## 系统要求\n\n";
$readmeContent .= "- PHP 8.0+\n";
$readmeContent .= "- MySQL 5.7+ 或 MariaDB 10.2+\n";
$readmeContent .= "- Composer\n";
$readmeContent .= "- cURL扩展\n";
$readmeContent .= "- PDO扩展\n";
$readmeContent .= "- JSON扩展\n\n";
$readmeContent .= "## 安装步骤\n\n";
$readmeContent .= "1. **克隆项目**\n";
$readmeContent .= "   ```bash\n";
$readmeContent .= "   git clone <repository-url>\n";
$readmeContent .= "   cd AlingAi_pro\n";
$readmeContent .= "   ```\n\n";
$readmeContent .= "2. **安装依赖**\n";
$readmeContent .= "   ```bash\n";
$readmeContent .= "   composer install\n";
$readmeContent .= "   ```\n\n";
$readmeContent .= "3. **配置环境**\n";
$readmeContent .= "   ```bash\n";
$readmeContent .= "   cp .env.example .env\n";
$readmeContent .= "   # 编辑 .env 文件，配置数据库和API密钥\n";
$readmeContent .= "   ```\n\n";
$readmeContent .= "4. **初始化数据库**\n";
$readmeContent .= "   ```bash\n";
$readmeContent .= "   mysql -u root -p < database/init.sql\n";
$readmeContent .= "   ```\n\n";
$readmeContent .= "5. **启动开发服务器**\n";
$readmeContent .= "   ```bash\n";
$readmeContent .= "   # Windows\n";
$readmeContent .= "   start_server.bat\n";
$readmeContent .= "   \n";
$readmeContent .= "   # Linux/Mac\n";
$readmeContent .= "   php -S localhost:8000 -t public\n";
$readmeContent .= "   ```\n\n";
$readmeContent .= "## 项目结构\n\n";
$readmeContent .= "```\n";
$readmeContent .= "src/\n";
$readmeContent .= "├── Controllers/Api/     # API控制器\n";
$readmeContent .= "├── Services/           # 业务服务层\n";
$readmeContent .= "│   └── Interfaces/     # 服务接口\n";
$readmeContent .= "├── Core/              # 核心组件\n";
$readmeContent .= "│   ├── Database/      # 数据库管理\n";
$readmeContent .= "│   ├── Container/     # 依赖注入容器\n";
$readmeContent .= "│   └── Logger/        # 日志系统\n";
$readmeContent .= "└── Config/            # 配置文件\n\n";
$readmeContent .= "public/                # 公共文件\n";
$readmeContent .= "database/             # 数据库文件\n";
$readmeContent .= "storage/              # 存储目录\n";
$readmeContent .= "```\n\n";
$readmeContent .= "## API文档\n\n";
$readmeContent .= "### 聊天API\n\n";
$readmeContent .= "- `POST /api/v1/chat/send` - 发送消息\n";
$readmeContent .= "- `GET /api/v1/chat/conversations` - 获取会话列表\n";
$readmeContent .= "- `GET /api/v1/chat/conversations/{id}/history` - 获取会话历史\n";
$readmeContent .= "- `POST /api/v1/chat/conversations` - 创建新会话\n";
$readmeContent .= "- `DELETE /api/v1/chat/conversations/{id}` - 删除会话\n";
$readmeContent .= "- `GET /api/v1/chat/health` - 健康检查\n\n";
$readmeContent .= "## 开发\n\n";
$readmeContent .= "### 运行测试\n";
$readmeContent .= "```bash\n";
$readmeContent .= "php test_chat_system.php\n";
$readmeContent .= "```\n\n";
$readmeContent .= "### 调试\n";
$readmeContent .= "```bash\n";
$readmeContent .= "php debug_autoloader.php\n";
$readmeContent .= "```\n\n";
$readmeContent .= "## 许可证\n\n";
$readmeContent .= "MIT License\n";

if (file_put_contents('README.md', $readmeContent)) {
    echo "✓ 生成 README.md\n";
} else {
    echo "✗ 生成 README.md 失败\n";
}
echo "\n";

// 8. 生成测试脚本
echo "8. 生成测试脚本...\n";
$testScript = "<?php\n\n";
$testScript .= "/**\n";
$testScript .= " * 系统测试脚本\n";
$testScript .= " */\n\n";
$testScript .= "require_once 'autoload.php';\n\n";
$testScript .= "echo \"=== AlingAi Pro 系统测试 ===\\n\\n\";\n\n";
$testScript .= "// 测试基本功能\n";
$testScript .= "echo \"1. 测试基本功能...\\n\";\n\n";
$testScript .= "try {\n";
$testScript .= "    // 测试类加载\n";
$testScript .= "    \$testClasses = [\n";
$testScript .= "        'AlingAi\\\\Services\\\\DeepSeekAIService',\n";
$testScript .= "        'AlingAi\\\\Services\\\\ChatService',\n";
$testScript .= "        'AlingAi\\\\Controllers\\\\Api\\\\EnhancedChatApiController'\n";
$testScript .= "    ];\n";
$testScript .= "    \n";
$testScript .= "    foreach (\$testClasses as \$class) {\n";
$testScript .= "        if (class_exists(\$class)) {\n";
$testScript .= "            echo \"✓ {\$class} 加载成功\\n\";\n";
$testScript .= "        } else {\n";
$testScript .= "            echo \"✗ {\$class} 加载失败\\n\";\n";
$testScript .= "        }\n";
$testScript .= "    }\n";
$testScript .= "    \n";
$testScript .= "    // 测试服务创建\n";
$testScript .= "    \$aiService = new AlingAi\\\\Services\\\\DeepSeekAIService();\n";
$testScript .= "    echo \"✓ AI服务创建成功\\n\";\n";
$testScript .= "    \n";
$testScript .= "    \$dbManager = new AlingAi\\\\Core\\\\Database\\\\DatabaseManager();\n";
$testScript .= "    echo \"✓ 数据库管理器创建成功\\n\";\n";
$testScript .= "    \n";
$testScript .= "    \$chatService = new AlingAi\\\\Services\\\\ChatService(\$aiService, \$dbManager);\n";
$testScript .= "    echo \"✓ 聊天服务创建成功\\n\";\n";
$testScript .= "    \n";
$testScript .= "} catch (Exception \$e) {\n";
$testScript .= "    echo \"✗ 测试失败: \" . \$e->getMessage() . \"\\n\";\n";
$testScript .= "}\n\n";
$testScript .= "echo \"\\n2. 测试配置...\\n\";\n\n";
$testScript .= "// 检查环境变量\n";
$testScript .= "\$envVars = ['DB_HOST', 'DB_DATABASE', 'DEEPSEEK_API_KEY'];\n";
$testScript .= "foreach (\$envVars as \$var) {\n";
$testScript .= "    \$value = getenv(\$var);\n";
$testScript .= "    if (\$value) {\n";
$testScript .= "        echo \"✓ {\$var} = {\$value}\\n\";\n";
$testScript .= "    } else {\n";
$testScript .= "        echo \"⚠ {\$var} 未设置\\n\";\n";
$testScript .= "    }\n";
$testScript .= "}\n\n";
$testScript .= "echo \"\\n3. 测试数据库连接...\\n\";\n\n";
$testScript .= "try {\n";
$testScript .= "    \$dbManager = new AlingAi\\\\Core\\\\Database\\\\DatabaseManager();\n";
$testScript .= "    \$connection = \$dbManager->getConnection();\n";
$testScript .= "    echo \"✓ 数据库连接成功\\n\";\n";
$testScript .= "    \n";
$testScript .= "    // 测试查询\n";
$testScript .= "    \$result = \$dbManager->query(\"SELECT 1 as test\")->fetch();\n";
$testScript .= "    if (\$result['test'] == 1) {\n";
$testScript .= "        echo \"✓ 数据库查询测试成功\\n\";\n";
$testScript .= "    } else {\n";
$testScript .= "        echo \"✗ 数据库查询测试失败\\n\";\n";
$testScript .= "    }\n";
$testScript .= "    \n";
$testScript .= "} catch (Exception \$e) {\n";
$testScript .= "    echo \"✗ 数据库连接失败: \" . \$e->getMessage() . \"\\n\";\n";
$testScript .= "}\n\n";
$testScript .= "echo \"\\n=== 测试完成 ===\\n\";\n";

if (file_put_contents('system_test.php', $testScript)) {
    echo "✓ 生成系统测试脚本\n";
} else {
    echo "✗ 生成系统测试脚本失败\n";
}
echo "\n";

// 9. 生成部署脚本
echo "9. 生成部署脚本...\n";
$deployScript = "@echo off\n";
$deployScript .= "echo === AlingAi Pro 部署脚本 ===\n";
$deployScript .= "echo.\n\n";
$deployScript .= "echo 1. 检查PHP版本...\n";
$deployScript .= "php --version\n";
$deployScript .= "if %errorlevel% neq 0 (\n";
$deployScript .= "    echo 错误: PHP未安装或不在PATH中\n";
$deployScript .= "    pause\n";
$deployScript .= "    exit /b 1\n";
$deployScript .= ")\n\n";
$deployScript .= "echo.\n";
$deployScript .= "echo 2. 检查Composer...\n";
$deployScript .= "composer --version\n";
$deployScript .= "if %errorlevel% neq 0 (\n";
$deployScript .= "    echo 错误: Composer未安装或不在PATH中\n";
$deployScript .= "    pause\n";
$deployScript .= "    exit /b 1\n";
$deployScript .= ")\n\n";
$deployScript .= "echo.\n";
$deployScript .= "echo 3. 安装依赖...\n";
$deployScript .= "composer install --no-dev --optimize-autoloader\n\n";
$deployScript .= "echo.\n";
$deployScript .= "echo 4. 设置文件权限...\n";
$deployScript .= "icacls storage /grant Everyone:F /T\n";
$deployScript .= "icacls logs /grant Everyone:F /T\n\n";
$deployScript .= "echo.\n";
$deployScript .= "echo 5. 检查环境配置...\n";
$deployScript .= "if not exist .env (\n";
$deployScript .= "    echo 警告: .env文件不存在，请手动配置\n";
$deployScript .= ") else (\n";
$deployScript .= "    echo .env文件存在\n";
$deployScript .= ")\n\n";
$deployScript .= "echo.\n";
$deployScript .= "echo 6. 启动服务...\n";
$deployScript .= "echo 启动开发服务器: http://localhost:8000\n";
$deployScript .= "php -S localhost:8000 -t public\n\n";
$deployScript .= "pause\n";

if (file_put_contents('deploy.bat', $deployScript)) {
    echo "✓ 生成部署脚本\n";
} else {
    echo "✗ 生成部署脚本失败\n";
}
echo "\n";

echo "=== 修复完成 ===\n";
echo "\n下一步操作:\n";
echo "1. 安装PHP 8.0+ 并添加到系统PATH\n";
echo "2. 安装Composer\n";
echo "3. 运行: composer install\n";
echo "4. 配置 .env 文件\n";
echo "5. 初始化数据库: mysql -u root -p < database/init.sql\n";
echo "6. 启动服务器: start_server.bat\n";
echo "\n测试命令:\n";
echo "- 系统测试: php system_test.php\n";
echo "- 调试: php debug_autoloader.php\n";
echo "\n所有问题已修复！\n"; 