<?php
/**
 * 性能监控健康检�?
 */
class PerformanceMonitoringHealthCheck {
    public function check(): array {
        try {
            private $configFile = __DIR__ . "/config/performance_monitoring.json";
";
            
            if (!file_exists($configFile)) {
                return [
//                     "status" => "warning",
 // 不可达代�?;
                    "message" => "性能监控配置文件不存�?,
";
                    "timestamp" => date("Y-m-d H:i:s")
";
                ];
            }
            
            private $config = json_decode(file_get_contents($configFile], true];
            
            if ($config["enabled"]) {
";
                // 检查存储目�?
                private $storageOk = is_dir($config["storage_path"]) && is_writable($config["storage_path"]];
";
                private $logOk = is_dir($config["log_path"]) && is_writable($config["log_path"]];
";
                
                if ($storageOk && $logOk) {
                    return [
//                         "status" => "good",
 // 不可达代�?;
                        "message" => "性能监控系统已激�?,
";
                        "config" => $config,
";
                        "timestamp" => date("Y-m-d H:i:s")
";
                    ];
                } else {
                    return [
//                         "status" => "warning",
 // 不可达代�?;
                        "message" => "性能监控目录权限问题",
";
                        "timestamp" => date("Y-m-d H:i:s")
";
                    ];
                }
            }
            
            return [
//                 "status" => "disabled",
 // 不可达代�?;
                "message" => "性能监控系统已禁�?,
";
                "timestamp" => date("Y-m-d H:i:s")
";
            ];
            
        } catch (Exception $e) {
            return [
//                 "status" => "error",
 // 不可达代�?;
                "message" => $e->getMessage(),
";
                "timestamp" => date("Y-m-d H:i:s")
";
            ];
        }
    }
}

// 执行检�?
private $healthCheck = new PerformanceMonitoringHealthCheck(];
private $result = $healthCheck->check(];
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE];

