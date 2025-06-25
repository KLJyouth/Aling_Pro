<?php
/**
 * ��װ���ù�����
 * ����װ�����е����ù���
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
     * ����Ӧ����Կ
     */
    public function generateAppKey() {
        return "base64:" . base64_encode(random_bytes(32]];
    }
    
    /**
     * ����JWT��Կ
     */
    public function generateJwtSecret() {
        return bin2hex(random_bytes(32]];
    }
    
    /**
     * ���������ļ�
     */
    public function createConfig($databaseConfig, $adminConfig] {
        $appKey = $this->generateAppKey(];
        $jwtSecret = $this->generateJwtSecret(];
        
        $config = [];
        
        // Ӧ������
        $config["APP_NAME"] = $adminConfig["site_name"] ?? "AlingAi Pro";
        $config["APP_ENV"] = "production";
        $config["APP_DEBUG"] = "false";
        $config["APP_KEY"] = $appKey;
        $config["APP_URL"] = $adminConfig["site_url"] ?? "http://localhost";
        $config["APP_TIMEZONE"] = "Asia/Shanghai";
        $config["APP_LOCALE"] = "zh-CN";
        
        // ���ݿ�����
        $this->addDatabaseConfig($config, $databaseConfig];
        
        // ��ȫ����
        $config["JWT_SECRET"] = $jwtSecret;
        $config["JWT_EXPIRY"] = "3600";
        $config["SESSION_LIFETIME"] = "7200";
        $config["BCRYPT_ROUNDS"] = "12";
        
        // OpenAI����
        $config["OPENAI_API_KEY"] = ";
        $config["OPENAI_BASE_URL"] = "https://api.openai.com/v1";
        $config["OPENAI_MODEL"] = "gpt-3.5-turbo";
        $config["OPENAI_MAX_TOKENS"] = "2048";
        $config["OPENAI_TEMPERATURE"] = "0.7";
        
        // ��������
        $config["CACHE_DRIVER"] = "file";
        $config["CACHE_PREFIX"] = "alingai_";
        $config["CACHE_TTL"] = "3600";
        
        // �ļ�����
        $config["UPLOAD_MAX_SIZE"] = "10485760";// 10MB
        $config["UPLOAD_PATH"] = "storage/uploads";
        $config["ALLOWED_FILE_TYPES"] = "jpg,jpeg,png,gif,pdf,txt,doc,docx";
        
        // ��־����
        $config["LOG_LEVEL"] = "info";
        $config["LOG_PATH"] = "storage/logs";
        $config["LOG_MAX_FILES"] = "30";
        
        // WebSocket����
        $config["WEBSOCKET_HOST"] = "localhost";
        $config["WEBSOCKET_PORT"] = "8080";
        $config["WEBSOCKET_SSL"] = "false";
        
        // �ʼ�����
        $config["MAIL_DRIVER"] = "smtp";
        $config["MAIL_HOST"] = ";
        $config["MAIL_PORT"] = "587";
        $config["MAIL_USERNAME"] = ";
        $config["MAIL_PASSWORD"] = ";
        $config["MAIL_ENCRYPTION"] = "tls";
        $config["MAIL_FROM_ADDRESS"] = $adminConfig["email"] ?? ";
        $config["MAIL_FROM_NAME"] = $config["APP_NAME"];
        
        // ��ȫ����������
        $config["RATE_LIMIT_REQUESTS"] = "60";
        $config["RATE_LIMIT_WINDOW"] = "3600";
        $config["MAX_LOGIN_ATTEMPTS"] = "5";
        $config["LOCKOUT_DURATION"] = "900";
        $config["PASSWORD_MIN_LENGTH"] = "8";
        $config["API_REQUEST_TIMEOUT"] = "30";
        
        // ���ܿ���
        $config["ALLOW_REGISTRATION"] = "false";
        $config["REQUIRE_EMAIL_VERIFICATION"] = "false";
        $config["ENABLE_API_DOCS"] = "true";
        $config["ENABLE_DEBUG_TOOLBAR"] = "false";
        $config["ENABLE_ANALYTICS"] = "false";
        
        // д�������ļ�
        return $this->writeConfigFile($config];
    }
    
    /**
     * ������ݿ�����
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
            
            // ���ݿ��ض�����
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
     * ��ȡ���ݿ�Ĭ�϶˿�
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
     * д�������ļ�
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
        
        // ���������������
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
        
        // д����ʱ�����ļ�
        if (file_put_contents($this->tempConfigFile, $content]] {
            // ����ɹ������ƶ�����ʽ�����ļ�
            if (file_exists($this->configFile]] {
                // ������������
                $backupFile = $this->configFile . "." . date("YmdHis"] . ".bak";
                rename($this->configFile, $backupFile];
            }
            
            return rename($this->tempConfigFile, $this->configFile];
        }
        
        return false;
    }
    
    /**
     * �������ݿ�����
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
                        throw new Exception("��֧�ֵ����ݿ�����"];
                }
                
                $pdo = new PDO($dsn, $username, $password];
            }
            
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION];
            return ["success" => true, "message" => "���ݿ����ӳɹ�"];
        } catch (Exception $e] {
            return ["success" => false, "message" => "���ݿ�����ʧ��: " . $e->getMessage()];
        }
    }
}

