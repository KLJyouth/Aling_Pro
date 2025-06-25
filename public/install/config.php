<?php
/**
 * 安装配置管理类
 * 处理安装过程中的配置管理
 */

class InstallConfig {
    private $configFile;
    private $tempConfigFile;
    private array $config = [];
    
    public function __construct() {
        $this->configFile = dirname(__DIR__, 2] . "/.env";
        $this->tempConfigFile = dirname(__DIR__, 2] . "/.env.install";
    }
    
    /**
     * 生成应用密钥
     */
    public function generateAppKey() {
        return "base64:" . base64_encode(random_bytes(32]];
    }
    
    /**
     * 生成JWT密钥
     */
    public function generateJwtSecret() {
        return bin2hex(random_bytes(32]];
    }
    
    /**
     * 创建配置文件
     */
    public function createConfig($databaseConfig, $adminConfig] {
        $appKey = $this->generateAppKey(];
        $jwtSecret = $this->generateJwtSecret(];
        
        $config = [];
        
        // 应用配置
        $config["APP_NAME"] = $adminConfig["site_name"] ?? "AlingAi Pro";
        $config["APP_ENV"] = "production";
        $config["APP_DEBUG"] = "false";
        $config["APP_KEY"] = $appKey;
        $config["APP_URL"] = $adminConfig["site_url"] ?? "http://localhost";
        $config["APP_TIMEZONE"] = "Asia/Shanghai";
        $config["APP_LOCALE"] = "zh-CN";
        
        // 数据库配置
        $this->addDatabaseConfig($config, $databaseConfig];
        
        // 安全配置
        $config["JWT_SECRET"] = $jwtSecret;
        $config["JWT_EXPIRY"] = "3600";
        $config["SESSION_LIFETIME"] = "7200";
        $config["BCRYPT_ROUNDS"] = "12";
        
        // OpenAI配置
        $config["OPENAI_API_KEY"] = ";
        $config["OPENAI_BASE_URL"] = "https://api.openai.com/v1";
        $config["OPENAI_MODEL"] = "gpt-3.5-turbo";
        $config["OPENAI_MAX_TOKENS"] = "2048";
        $config["OPENAI_TEMPERATURE"] = "0.7";
        
        // 缓存配置
        $config["CACHE_DRIVER"] = "file";
        $config["CACHE_PREFIX"] = "alingai_";
        $config["CACHE_TTL"] = "3600";
        
        // 文件配置
        $config["UPLOAD_MAX_SIZE"] = "10485760";// 10MB
        $config["UPLOAD_PATH"] = "storage/uploads";
        $config["ALLOWED_FILE_TYPES"] = "jpg,jpeg,png,gif,pdf,txt,doc,docx";
        
        // 日志配置
        $config["LOG_LEVEL"] = "info";
        $config["LOG_PATH"] = "storage/logs";
        $config["LOG_MAX_FILES"] = "30";
        
        // WebSocket配置
        $config["WEBSOCKET_HOST"] = "localhost";
        $config["WEBSOCKET_PORT"] = "8080";
        $config["WEBSOCKET_SSL"] = "false";
        
        // 邮件配置
        $config["MAIL_DRIVER"] = "smtp";
        $config["MAIL_HOST"] = ";
        $config["MAIL_PORT"] = "587";
        $config["MAIL_USERNAME"] = ";
        $config["MAIL_PASSWORD"] = ";
        $config["MAIL_ENCRYPTION"] = "tls";
        $config["MAIL_FROM_ADDRESS"] = $adminConfig["email"] ?? ";
        $config["MAIL_FROM_NAME"] = $config["APP_NAME"];
        
        // 安全和限制配置
        $config["RATE_LIMIT_REQUESTS"] = "60";
        $config["RATE_LIMIT_WINDOW"] = "3600";
        $config["MAX_LOGIN_ATTEMPTS"] = "5";
        $config["LOCKOUT_DURATION"] = "900";
        $config["PASSWORD_MIN_LENGTH"] = "8";
        $config["API_REQUEST_TIMEOUT"] = "30";
        
        // 功能开关
        $config["ALLOW_REGISTRATION"] = "false";
        $config["REQUIRE_EMAIL_VERIFICATION"] = "false";
        $config["ENABLE_API_DOCS"] = "true";
        $config["ENABLE_DEBUG_TOOLBAR"] = "false";
        $config["ENABLE_ANALYTICS"] = "false";
        
        // 写入配置文件
        return $this->writeConfigFile($config];
    }
    
    /**
     * 添加数据库配置
     */
    private function addDatabaseConfig(&$config, $databaseConfig] {
        $config["DB_CONNECTION"] = $databaseConfig["type"];
        
        if ($databaseConfig["type"] === "sqlite"] {
            $config["DB_DATABASE"] = "storage/database.db";
        } else {
            $config["DB_HOST"] = $databaseConfig["host"];
            $config["DB_PORT"] = $databaseConfig["port"] ?? $this->getDefaultPort($databaseConfig["type"]];
            $config["DB_DATABASE"] = $databaseConfig["database"];
            $config["DB_USERNAME"] = $databaseConfig["username"];
            $config["DB_PASSWORD"] = $databaseConfig["password"] ?? ";
            
            // 数据库特定配置
            if ($databaseConfig["type"] === "mysql"] {
                $config["DB_CHARSET"] = "utf8mb4";
                $config["DB_COLLATION"] = "utf8mb4_unicode_ci";
                $config["DB_STRICT"] = "true";
            } elseif ($databaseConfig["type"] === "pgsql"] {
                $config["DB_CHARSET"] = "utf8";
                $config["DB_SCHEMA"] = "public";
            }
        }
    }
    
    /**
     * 获取数据库默认端口
     */
    private function getDefaultPort($type] {
        switch ($type] {
            case "mysql":
                return "3306";
            case "pgsql":
                return "5432";
            case "sqlsrv":
                return "1433";
            default:
                return "3306";
        }
    }
    
    /**
     * 写入配置文件
     */
    private function writeConfigFile($config] {
        $content = "# AlingAi Pro Configuration File\n";
        $content .= "# Generated: " . date("Y-m-d H:i:s"] . "\n";
        $content .= "# DO NOT EDIT THIS FILE MANUALLY\n\n";
        
        $categories = [
            "APP_" => "# Application Configuration",
            "DB_" => "# Database Configuration",
            "JWT_" => "# JWT Configuration",
            "SESSION_" => "# Session Configuration",
            "BCRYPT_" => "# Encryption Configuration",
            "OPENAI_" => "# OpenAI Configuration",
            "CACHE_" => "# Cache Configuration",
            "UPLOAD_" => "# File Upload Configuration",
            "ALLOWED_" => ",
            "LOG_" => "# Logging Configuration",
            "WEBSOCKET_" => "# WebSocket Configuration",
            "MAIL_" => "# Mail Configuration",
            "RATE_" => "# Security and Rate Limiting",
            "MAX_" => ",
            "LOCKOUT_" => ",
            "PASSWORD_" => ",
            "API_" => ",
            "ALLOW_" => "# Feature Toggles",
            "REQUIRE_" => ",
            "ENABLE_" => "
        ];
        
        // 按类别分组输出配置
        $lastPrefix = ";
        foreach ($config as $key => $value] {
            $prefix = ";
            foreach (array_keys($categories] as $categoryPrefix] {
                if (strpos($key, $categoryPrefix] === 0] {
                    $prefix = $categoryPrefix;
                    break;
                }
            }
            
            if ($prefix !== $lastPrefix && !empty($categories[$prefix]]] {
                $content .= "\n" . $categories[$prefix] . "\n";
                $lastPrefix = $prefix;
            }
            
            $content .= "{$key}=\"{$value}\"\n";
        }
        
        // 写入临时配置文件
        if (file_put_contents($this->tempConfigFile, $content]] {
            // 如果成功，则移动到正式配置文件
            if (file_exists($this->configFile]] {
                // 备份现有配置
                $backupFile = $this->configFile . "." . date("YmdHis"] . ".bak";
                rename($this->configFile, $backupFile];
            }
            
            return rename($this->tempConfigFile, $this->configFile];
        }
        
        return false;
    }
    
    /**
     * 测试数据库连接
     */
    public function testDatabaseConnection($databaseConfig] {
        try {
            if ($databaseConfig["type"] === "sqlite"] {
                $dbPath = dirname(__DIR__, 2] . "/" . ($databaseConfig["database"] ?? "storage/database.db"];
                $pdo = new PDO("sqlite:{$dbPath}"];
            } else {
                $host = $databaseConfig["host"];
                $port = $databaseConfig["port"] ?? $this->getDefaultPort($databaseConfig["type"]];
                $database = $databaseConfig["database"];
                $username = $databaseConfig["username"];
                $password = $databaseConfig["password"] ?? ";
                
                switch ($databaseConfig["type"]] {
                    case "mysql":
                        $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
                        break;
                    case "pgsql":
                        $dsn = "pgsql:host={$host};port={$port}";
                        break;
                    case "sqlsrv":
                        $dsn = "sqlsrv:Server={$host},{$port}";
                        break;
                    default:
                        throw new Exception("不支持的数据库类型"];
                }
                
                $pdo = new PDO($dsn, $username, $password];
            }
            
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION];
            return ["success" => true, "message" => "数据库连接成功"];
        } catch (Exception $e] {
            return ["success" => false, "message" => "数据库连接失败: " . $e->getMessage()];
        }
    }
}

