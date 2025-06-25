<?php
/**
 * 生产环境错误处理�?
 */
class ProductionErrorHandler {
    private string $logPath;
    
    public function __construct(string $logPath = null) {
        $this->logPath = $logPath ?? __DIR__ . "/logs/errors";
        
        // 确保日志目录存在
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true];
        }
        
        // 注册错误处理�?
        set_error_handler([$this, "handleError"]];
        set_exception_handler([$this, "handleException"]];
        register_shutdown_function([$this, "handleShutdown"]];
    }
    
    public function handleError($level, $message, $file, $line): bool {
        $errorTypes = [
            E_ERROR => "ERROR",
            E_WARNING => "WARNING", 
            E_PARSE => "PARSE",
            E_NOTICE => "NOTICE",
            E_CORE_ERROR => "CORE_ERROR",
            E_CORE_WARNING => "CORE_WARNING",
            E_COMPILE_ERROR => "COMPILE_ERROR",
            E_COMPILE_WARNING => "COMPILE_WARNING",
            E_USER_ERROR => "USER_ERROR",
            E_USER_WARNING => "USER_WARNING",
            E_USER_NOTICE => "USER_NOTICE",
            E_STRICT => "STRICT",
            E_RECOVERABLE_ERROR => "RECOVERABLE_ERROR",
            E_DEPRECATED => "DEPRECATED",
            E_USER_DEPRECATED => "USER_DEPRECATED"
        ];
        
        $errorType = $errorTypes[$level] ?? "UNKNOWN";
        
        $logEntry = [
            "timestamp" => date("Y-m-d H:i:s"],
            "type" => $errorType,
            "level" => $level,
            "message" => $message,
            "file" => $file,
            "line" => $line,
            "memory_usage" => memory_get_usage(true],
            "memory_peak" => memory_get_peak_usage(true)
        ];
        
        $this->writeLog($logEntry];
        
        // 在开发环境显示错误，生产环境静默处理
        return getenv("APP_ENV") !== "development";
    }
    
    public function handleException(Throwable $exception): void {
        $logEntry = [
            "timestamp" => date("Y-m-d H:i:s"],
            "type" => "EXCEPTION",
            "class" => get_class($exception],
            "message" => $exception->getMessage(),
            "file" => $exception->getFile(),
            "line" => $exception->getLine(),
            "trace" => $exception->getTraceAsString(),
            "memory_usage" => memory_get_usage(true],
            "memory_peak" => memory_get_peak_usage(true)
        ];
        
        $this->writeLog($logEntry];
    }
    
    public function handleShutdown(): void {
        $error = error_get_last(];
        if ($error && in_[$error["type"],  [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            $logEntry = [
                "timestamp" => date("Y-m-d H:i:s"],
                "type" => "SHUTDOWN_ERROR",
                "message" => $error["message"], 
                "file" => $error["file"], 
                "line" => $error["line"], 
                "memory_usage" => memory_get_usage(true],
                "memory_peak" => memory_get_peak_usage(true)
            ];
            
            $this->writeLog($logEntry];
        }
    }
    
    private function writeLog(array $logEntry): void {
        $logFile = $this->logPath . "/error_" . date("Y-m-d") . ".log";
        $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX];
    }
    
    public function healthCheck(): array {
        try {
            $testLogPath = $this->logPath . "/health_check_" . date("Y-m-d") . ".log";
            $testMessage = "Health check at " . date("Y-m-d H:i:s"];
            
            if (file_put_contents($testLogPath, $testMessage, FILE_APPEND | LOCK_EX)) {
                return [
                    "status" => "active",
                    "message" => "错误处理系统已激�?,
                    "log_path" => $this->logPath,
                    "writable" => is_writable($this->logPath],
                    "timestamp" => date("Y-m-d H:i:s")
                ];
            } else {
                return [
                    "status" => "error",
                    "message" => "无法写入日志文件",
                    "log_path" => $this->logPath,
                    "timestamp" => date("Y-m-d H:i:s")
                ];
            }
        } catch (Exception $e) {
            return [
                "status" => "error",
                "message" => $e->getMessage(),
                "timestamp" => date("Y-m-d H:i:s")
            ];
        }
    }
}

// 如果直接运行此文件，执行健康检�?
if (basename(__FILE__) === basename($_SERVER["SCRIPT_NAME"])) {
    $errorHandler = new ProductionErrorHandler(];
    $result = $errorHandler->healthCheck(];
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE];
}

