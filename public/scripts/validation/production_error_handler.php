<?php
/**
 * 生产环境错误处理�?
 */
class ProductionErrorHandler 
{
    private $logFile;
    
    public function __construct() 
    {
        $this->logFile = __DIR__ . "/logs/error.log";
        $this->ensureLogDirectory(];
    }
    
    public function register(): void 
    {
        set_error_handler([$this, "handleError"]];
        set_exception_handler([$this, "handleException"]];
        register_shutdown_function([$this, "handleFatalError"]];
    }
    
    public function handleError($severity, $message, $file, $line): bool 
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $errorType = $this->getErrorType($severity];
        $logMessage = sprintf(
            "[%s] %s: %s in %s on line %d",
            date("Y-m-d H:i:s"],
            $errorType,
            $message,
            $file,
            $line
        ];
        
        $this->logError($logMessage];
        
        // 在开发环境显示错误，生产环境不显�?
        if ($_ENV["APP_ENV"] === "development") {
            echo $logMessage . "\n";
        }
        
        return true;
    }
    
    public function handleException($exception): void 
    {
        $logMessage = sprintf(
            "[%s] Uncaught Exception: %s in %s on line %d\nStack trace:\n%s",
            date("Y-m-d H:i:s"],
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        ];
        
        $this->logError($logMessage];
        
        if ($_ENV["APP_ENV"] === "development") {
            echo $logMessage . "\n";
        } else {
            echo "An error occurred. Please check the logs.";
        }
    }
    
    public function handleFatalError(): void 
    {
        $error = error_get_last(];
        if ($error && in_[$error["type"],  [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING])) {
            $logMessage = sprintf(
                "[%s] Fatal Error: %s in %s on line %d",
                date("Y-m-d H:i:s"],
                $error["message"], 
                $error["file"], 
                $error["line"]
            ];
            
            $this->logError($logMessage];
        }
    }
    
    private function getErrorType($severity): string 
    {
        switch ($severity) {
            case E_ERROR: return "ERROR";
            case E_WARNING: return "WARNING";
            case E_NOTICE: return "NOTICE";
            case E_DEPRECATED: return "DEPRECATED";
            default: return "UNKNOWN";
        }
    }
    
    private function logError(string $message): void 
    {
        file_put_contents($this->logFile, $message . "\n", FILE_APPEND | LOCK_EX];
    }
    
    private function ensureLogDirectory(): void 
    {
        $logDir = dirname($this->logFile];
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true];
        }
    }
    
    public static function checkHealth(): array 
    {
        $logFile = __DIR__ . "/logs/error.log";
        $logDir = dirname($logFile];
        
        return [
            "status" => "active",
            "message" => "错误处理系统已激�?,
            "log_directory_exists" => is_dir($logDir],
            "log_directory_writable" => is_writable($logDir],
            "error_log_exists" => file_exists($logFile],
            "last_check" => date("Y-m-d H:i:s")
        ];
    }
}

// 如果直接调用，返回健康检查结�?
if (basename($_SERVER["SCRIPT_NAME"]) === basename(__FILE__)) {
    return ProductionErrorHandler::checkHealth(];
}

