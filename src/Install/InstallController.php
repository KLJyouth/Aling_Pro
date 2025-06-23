<?php

namespace AlingAi\Install;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use AlingAi\Security\EncryptionService;
use AlingAi\Services\DatabaseService;

/**
 * 安装程序控制器
 * 
 * 处理系统安装流程
 * 
 * @package AlingAi\Install
 * @version 6.0.0
 */
class InstallController
{
    private LoggerInterface $logger;
    private array $config;
    private ?EncryptionService $encryption = null;
    
    /**
     * 构造函数
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->config = $this->loadConfig();
    }
    
    /**
     * 加载配置
     */
    private function loadConfig(): array
    {
        return [
            'app_name' => $_ENV['APP_NAME'] ?? 'AlingAi Pro',
            'app_version' => $_ENV['APP_VERSION'] ?? '6.0.0',
            'min_php_version' => '8.0.0',
            'required_extensions' => [
                'pdo',
                'pdo_mysql',
                'pdo_sqlite',
                'mbstring',
                'json',
                'openssl',
                'gd',
                'curl',
                'xml',
                'zip'
            ],
            'required_functions' => [
                'password_hash',
                'random_bytes',
                'openssl_encrypt'
            ],
            'writable_paths' => [
                'storage',
                'storage/logs',
                'storage/data',
                'storage/cache',
                'storage/sessions',
                'storage/uploads',
                '.env'
            ]
        ];
    }
    
    /**
     * 显示安装页面
     * 
     * @param ServerRequestInterface $request 请求
     * @param ResponseInterface $response 响应
     * @return ResponseInterface 响应
     */
    public function showInstallPage(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // 检查是否已安装
        if ($this->isInstalled()) {
            return $this->redirectToHome($response);
        }
        
        // 渲染安装页面
        $html = $this->renderInstallTemplate();
        $response->getBody()->write($html);
        
        return $response->withHeader('Content-Type', 'text/html');
    }
    
    /**
     * 处理安装请求
     * 
     * @param ServerRequestInterface $request 请求
     * @param ResponseInterface $response 响应
     * @return ResponseInterface 响应
     */
    public function handleInstall(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();
            
            // 验证请求数据
            $this->validateInstallData($data);
            
            // 执行安装步骤
            $result = $this->performInstallation($data);
            
            // 返回成功响应
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => '安装成功完成',
                'result' => $result
            ]));
            
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            // 记录错误
            $this->logger->error('安装失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // 返回错误响应
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '安装失败: ' . $e->getMessage()
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(500);
        }
    }
    
    /**
     * 处理系统检查请求
     * 
     * @param ServerRequestInterface $request 请求
     * @param ResponseInterface $response 响应
     * @return ResponseInterface 响应
     */
    public function handleSystemCheck(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            // 执行系统检查
            $result = $this->performSystemCheck();
            
            // 返回检查结果
            $response->getBody()->write(json_encode([
                'success' => true,
                'checks' => $result
            ]));
            
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            // 返回错误响应
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '系统检查失败: ' . $e->getMessage()
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(500);
        }
    }
    
    /**
     * 检查安装状态
     * 
     * @param ServerRequestInterface $request 请求
     * @param ResponseInterface $response 响应
     * @return ResponseInterface 响应
     */
    public function checkInstallStatus(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $installed = $this->isInstalled();
        
        $response->getBody()->write(json_encode([
            'installed' => $installed,
            'version' => $this->config['app_version']
        ]));
        
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * 验证安装数据
     * 
     * @param array $data 安装数据
     * @throws \Exception 验证失败时抛出异常
     */
    private function validateInstallData(array $data): void
    {
        // 检查必要字段
        $requiredSections = ['database', 'admin', 'app'];
        foreach ($requiredSections as $section) {
            if (!isset($data[$section]) || !is_array($data[$section])) {
                throw new \Exception("缺少必要配置部分: {$section}");
            }
        }
        
        // 验证数据库配置
        $this->validateDatabaseConfig($data['database']);
        
        // 验证管理员配置
        $this->validateAdminConfig($data['admin']);
        
        // 验证应用配置
        $this->validateAppConfig($data['app']);
    }
    
    /**
     * 验证数据库配置
     * 
     * @param array $config 数据库配置
     * @throws \Exception 验证失败时抛出异常
     */
    private function validateDatabaseConfig(array $config): void
    {
        // 检查数据库类型
        if (!isset($config['type']) || !in_array($config['type'], ['mysql', 'sqlite'])) {
            throw new \Exception('不支持的数据库类型');
        }
        
        // MySQL 特定验证
        if ($config['type'] === 'mysql') {
            $requiredFields = ['host', 'port', 'database', 'username'];
            foreach ($requiredFields as $field) {
                if (!isset($config[$field]) || empty($config[$field])) {
                    throw new \Exception("MySQL配置缺少必要字段: {$field}");
                }
            }
        }
        
        // SQLite 特定验证
        if ($config['type'] === 'sqlite') {
            if (!isset($config['database']) || empty($config['database'])) {
                throw new \Exception("SQLite配置缺少必要字段: database");
            }
        }
    }
    
    /**
     * 验证管理员配置
     * 
     * @param array $config 管理员配置
     * @throws \Exception 验证失败时抛出异常
     */
    private function validateAdminConfig(array $config): void
    {
        // 检查必要字段
        $requiredFields = ['email', 'password'];
        foreach ($requiredFields as $field) {
            if (!isset($config[$field]) || empty($config[$field])) {
                throw new \Exception("管理员配置缺少必要字段: {$field}");
            }
        }
        
        // 验证邮箱格式
        if (!filter_var($config['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('无效的管理员邮箱格式');
        }
        
        // 验证密码长度
        if (strlen($config['password']) < 8) {
            throw new \Exception('管理员密码长度不能少于8个字符');
        }
    }
    
    /**
     * 验证应用配置
     * 
     * @param array $config 应用配置
     * @throws \Exception 验证失败时抛出异常
     */
    private function validateAppConfig(array $config): void
    {
        // 检查必要字段
        $requiredFields = ['name', 'url'];
        foreach ($requiredFields as $field) {
            if (!isset($config[$field]) || empty($config[$field])) {
                throw new \Exception("应用配置缺少必要字段: {$field}");
            }
        }
        
        // 验证URL格式
        if (!filter_var($config['url'], FILTER_VALIDATE_URL)) {
            throw new \Exception('无效的应用URL格式');
        }
    }
    
    /**
     * 执行安装步骤
     * 
     * @param array $data 安装数据
     * @return array 执行结果
     * @throws \Exception 执行失败时抛出异常
     */
    private function performInstallation(array $data): array
    {
        // 创建安装器实例
        $installer = new Installer($this->logger);
        
        // 执行系统检查
        $installer->executeStep('system_check', $data);
        
        // 创建配置文件
        $installer->executeStep('create_config', $data);
        
        // 设置数据库
        $installer->executeStep('setup_database', $data);
        
        // 创建数据表
        $installer->executeStep('create_tables', $data);
        
        // 创建管理员账户
        $installer->executeStep('create_admin', $data);
        
        // 设置加密
        $installer->executeStep('setup_encryption', $data);
        
        // 完成安装
        $result = $installer->executeStep('finalize', $data);
        
        // 创建安装锁定文件
        $this->createInstallLock();
        
        return $result;
    }
    
    /**
     * 执行系统检查
     * 
     * @return array 检查结果
     * @throws \Exception 检查失败时抛出异常
     */
    private function performSystemCheck(): array
    {
        $results = [];
        
        // 检查PHP版本
        $phpVersion = PHP_VERSION;
        $phpVersionValid = version_compare($phpVersion, $this->config['min_php_version'], '>=');
        $results['php_version'] = [
            'current' => $phpVersion,
            'required' => $this->config['min_php_version'],
            'valid' => $phpVersionValid
        ];
        
        if (!$phpVersionValid) {
            throw new \Exception("PHP版本不满足要求，当前版本: {$phpVersion}，需要: {$this->config['min_php_version']}");
        }
        
        // 检查PHP扩展
        $extensions = [];
        foreach ($this->config['required_extensions'] as $extension) {
            $loaded = extension_loaded($extension);
            $extensions[$extension] = $loaded;
            
            if (!$loaded) {
                throw new \Exception("缺少必要的PHP扩展: {$extension}");
            }
        }
        $results['extensions'] = $extensions;
        
        // 检查PHP函数
        $functions = [];
        foreach ($this->config['required_functions'] as $function) {
            $exists = function_exists($function);
            $functions[$function] = $exists;
            
            if (!$exists) {
                throw new \Exception("缺少必要的PHP函数: {$function}");
            }
        }
        $results['functions'] = $functions;
        
        // 检查目录权限
        $paths = [];
        foreach ($this->config['writable_paths'] as $path) {
            $fullPath = dirname(__DIR__, 2) . '/' . $path;
            
            // 如果目录不存在，尝试创建
            if (!file_exists($fullPath) && strpos($path, '.') === false) {
                @mkdir($fullPath, 0755, true);
            }
            
            $writable = is_writable($fullPath) || (!file_exists($fullPath) && is_writable(dirname($fullPath)));
            $paths[$path] = $writable;
            
            if (!$writable) {
                throw new \Exception("目录不可写: {$path}");
            }
        }
        $results['paths'] = $paths;
        
        return $results;
    }
    
    /**
     * 创建安装锁定文件
     * 
     * @throws \Exception 创建失败时抛出异常
     */
    private function createInstallLock(): void
    {
        $lockFile = dirname(__DIR__, 2) . '/storage/install.lock';
        $content = json_encode([
            'version' => $this->config['app_version'],
            'installed_at' => date('Y-m-d H:i:s'),
            'installer' => 'AlingAi Installer'
        ]);
        
        if (file_put_contents($lockFile, $content) === false) {
            throw new \Exception('无法创建安装锁定文件');
        }
    }
    
    /**
     * 检查是否已安装
     * 
     * @return bool 是否已安装
     */
    private function isInstalled(): bool
    {
        $lockFile = dirname(__DIR__, 2) . '/storage/install.lock';
        return file_exists($lockFile);
    }
    
    /**
     * 重定向到首页
     * 
     * @param ResponseInterface $response 响应
     * @return ResponseInterface 响应
     */
    private function redirectToHome(ResponseInterface $response): ResponseInterface
    {
        return $response->withHeader('Location', '/')
                       ->withStatus(302);
    }
    
    /**
     * 渲染安装模板
     * 
     * @return string HTML内容
     */
    private function renderInstallTemplate(): string
    {
        $templatePath = dirname(__DIR__, 2) . '/templates/install/index.html';
        
        if (file_exists($templatePath)) {
            return file_get_contents($templatePath);
        }
        
        // 如果模板不存在，返回默认HTML
        return $this->getDefaultInstallHtml();
    }
    
    /**
     * 获取默认安装HTML
     * 
     * @return string HTML内容
     */
    private function getDefaultInstallHtml(): string
    {
        return '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>安装 AlingAi Pro ' . htmlspecialchars($this->config['app_version']) . '</title>
    <style>
        body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1 { color: #2c3e50; text-align: center; margin-bottom: 30px; }
        .step { display: none; background: #f9f9f9; border-radius: 5px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .step.active { display: block; }
        .step-title { font-size: 1.2em; font-weight: bold; margin-bottom: 15px; color: #3498db; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background: #3498db; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; }
        button:hover { background: #2980b9; }
        .btn-next { float: right; }
        .btn-prev { float: left; }
        .clearfix::after { content: ""; clear: both; display: table; }
        .progress-bar { height: 5px; background: #ecf0f1; margin-bottom: 20px; border-radius: 5px; overflow: hidden; }
        .progress { height: 100%; background: #3498db; width: 0; transition: width 0.3s; }
        .error { color: #e74c3c; font-size: 0.9em; margin-top: 5px; }
        .success { color: #2ecc71; }
    </style>
</head>
<body>
    <h1>安装 AlingAi Pro ' . htmlspecialchars($this->config['app_version']) . '</h1>
    
    <div class="progress-bar">
        <div class="progress" id="progress"></div>
    </div>
    
    <div class="step active" id="step1">
        <div class="step-title">步骤 1: 系统检查</div>
        <div id="system-check-results">正在检查系统环境...</div>
        <div class="clearfix">
            <button class="btn-next" id="btn-step1-next" disabled>下一步</button>
        </div>
    </div>
    
    <div class="step" id="step2">
        <div class="step-title">步骤 2: 数据库设置</div>
        <div class="form-group">
            <label for="db-type">数据库类型</label>
            <select id="db-type" name="db_type">
                <option value="mysql">MySQL</option>
                <option value="sqlite">SQLite</option>
            </select>
        </div>
        <div id="mysql-settings">
            <div class="form-group">
                <label for="db-host">数据库主机</label>
                <input type="text" id="db-host" name="db_host" value="localhost">
            </div>
            <div class="form-group">
                <label for="db-port">数据库端口</label>
                <input type="text" id="db-port" name="db_port" value="3306">
            </div>
            <div class="form-group">
                <label for="db-name">数据库名称</label>
                <input type="text" id="db-name" name="db_name" value="alingai_pro">
            </div>
            <div class="form-group">
                <label for="db-user">数据库用户名</label>
                <input type="text" id="db-user" name="db_user" value="root">
            </div>
            <div class="form-group">
                <label for="db-pass">数据库密码</label>
                <input type="password" id="db-pass" name="db_pass">
            </div>
        </div>
        <div class="clearfix">
            <button class="btn-prev" id="btn-step2-prev">上一步</button>
            <button class="btn-next" id="btn-step2-next">下一步</button>
        </div>
    </div>
    
    <div class="step" id="step3">
        <div class="step-title">步骤 3: 管理员账号</div>
        <div class="form-group">
            <label for="admin-email">管理员邮箱</label>
            <input type="email" id="admin-email" name="admin_email" required>
        </div>
        <div class="form-group">
            <label for="admin-password">管理员密码</label>
            <input type="password" id="admin-password" name="admin_password" required>
        </div>
        <div class="form-group">
            <label for="admin-password-confirm">确认密码</label>
            <input type="password" id="admin-password-confirm" name="admin_password_confirm" required>
        </div>
        <div class="clearfix">
            <button class="btn-prev" id="btn-step3-prev">上一步</button>
            <button class="btn-next" id="btn-step3-next">下一步</button>
        </div>
    </div>
    
    <div class="step" id="step4">
        <div class="step-title">步骤 4: 应用设置</div>
        <div class="form-group">
            <label for="app-name">应用名称</label>
            <input type="text" id="app-name" name="app_name" value="AlingAi Pro">
        </div>
        <div class="form-group">
            <label for="app-url">应用URL</label>
            <input type="url" id="app-url" name="app_url" value="http://localhost">
        </div>
        <div class="form-group">
            <label for="app-timezone">时区</label>
            <select id="app-timezone" name="app_timezone">
                <option value="Asia/Shanghai">Asia/Shanghai (北京时间)</option>
                <option value="UTC">UTC</option>
                <option value="America/New_York">America/New_York</option>
                <option value="Europe/London">Europe/London</option>
            </select>
        </div>
        <div class="clearfix">
            <button class="btn-prev" id="btn-step4-prev">上一步</button>
            <button class="btn-next" id="btn-step4-next">安装</button>
        </div>
    </div>
    
    <div class="step" id="step5">
        <div class="step-title">步骤 5: 安装进度</div>
        <div id="install-progress">准备安装...</div>
        <div class="clearfix">
            <button class="btn-finish" id="btn-finish" style="display: none;">完成</button>
        </div>
    </div>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 步骤控制
            let currentStep = 1;
            const totalSteps = 5;
            
            // 系统检查
            checkSystem();
            
            // 步骤按钮事件
            document.getElementById("btn-step1-next").addEventListener("click", () => goToStep(2));
            document.getElementById("btn-step2-prev").addEventListener("click", () => goToStep(1));
            document.getElementById("btn-step2-next").addEventListener("click", () => goToStep(3));
            document.getElementById("btn-step3-prev").addEventListener("click", () => goToStep(2));
            document.getElementById("btn-step3-next").addEventListener("click", () => goToStep(4));
            document.getElementById("btn-step4-prev").addEventListener("click", () => goToStep(3));
            document.getElementById("btn-step4-next").addEventListener("click", startInstallation);
            document.getElementById("btn-finish").addEventListener("click", () => window.location.href = "/");
            
            // 数据库类型切换
            document.getElementById("db-type").addEventListener("change", function() {
                const mysqlSettings = document.getElementById("mysql-settings");
                mysqlSettings.style.display = this.value === "mysql" ? "block" : "none";
            });
            
            function goToStep(step) {
                document.getElementById(`step${currentStep}`).classList.remove("active");
                document.getElementById(`step${step}`).classList.add("active");
                currentStep = step;
                updateProgress();
            }
            
            function updateProgress() {
                const percent = ((currentStep - 1) / (totalSteps - 1)) * 100;
                document.getElementById("progress").style.width = `${percent}%`;
            }
            
            function checkSystem() {
                const resultsDiv = document.getElementById("system-check-results");
                resultsDiv.innerHTML = "正在检查系统环境...";
                
                fetch("/install/system-check")
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            let html = "<div class=\'success\'>系统检查通过！</div><ul>";
                            for (const check in data.checks) {
                                html += `<li>${check}: <span class="success">✓</span></li>`;
                            }
                            html += "</ul>";
                            resultsDiv.innerHTML = html;
                            document.getElementById("btn-step1-next").disabled = false;
                        } else {
                            resultsDiv.innerHTML = `<div class="error">系统检查失败: ${data.message}</div>`;
                        }
                    })
                    .catch(error => {
                        resultsDiv.innerHTML = `<div class="error">检查时发生错误: ${error.message}</div>`;
                    });
            }
            
            function startInstallation() {
                goToStep(5);
                const progressDiv = document.getElementById("install-progress");
                progressDiv.innerHTML = "正在安装...";
                
                // 收集表单数据
                const formData = {
                    database: {
                        type: document.getElementById("db-type").value,
                        host: document.getElementById("db-host").value,
                        port: document.getElementById("db-port").value,
                        database: document.getElementById("db-name").value,
                        username: document.getElementById("db-user").value,
                        password: document.getElementById("db-pass").value
                    },
                    admin: {
                        email: document.getElementById("admin-email").value,
                        password: document.getElementById("admin-password").value
                    },
                    app: {
                        name: document.getElementById("app-name").value,
                        url: document.getElementById("app-url").value,
                        timezone: document.getElementById("app-timezone").value
                    }
                };
                
                // 发送安装请求
                fetch("/install/perform", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        progressDiv.innerHTML = `<div class="success">安装成功完成！</div>
                            <p>您可以现在开始使用 AlingAi Pro。</p>`;
                        document.getElementById("btn-finish").style.display = "block";
                    } else {
                        progressDiv.innerHTML = `<div class="error">安装失败: ${data.message}</div>`;
                    }
                })
                .catch(error => {
                    progressDiv.innerHTML = `<div class="error">安装时发生错误: ${error.message}</div>`;
                });
            }
        });
    </script>
</body>
</html>';
    }
} 