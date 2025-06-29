<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class DatabaseSecurityService
{
    /**
     * 防爆破攻击配置
     */
    protected $bruteForceConfig;
    
    /**
     * SQL注入防御配置
     */
    protected $sqlInjectionConfig;
    
    /**
     * 连接限制配置
     */
    protected $connectionLimitConfig;

    /**
     * 初始化数据库安全服务
     */
    public function __construct()
    {
        // 从配置文件加载设置
        $this->bruteForceConfig = Config::get("database_security.brute_force", [
            "enabled" => true,
            "max_failed_attempts" => 5,
            "lockout_time" => 30,
            "detection_window" => 5,
            "ip_blacklist_threshold" => 10,
            "blacklist_duration" => 1440,
        ]);
        
        $this->sqlInjectionConfig = Config::get("database_security.sql_injection", [
            "enabled" => true,
            "log_suspicious_queries" => true,
            "block_suspicious_queries" => true,
            "alert_threshold" => 3,
        ]);
        
        $this->connectionLimitConfig = Config::get("database_security.connection_limits", [
            "enabled" => true,
            "max_connections_per_ip" => 20,
            "max_connections_total" => 100,
            "connection_timeout" => 300,
        ]);
    }

    /**
     * 检测并防御暴力破解攻击
     *
     * @param string $ipAddress IP地址
     * @param string $username 用户名
     * @param bool $isSuccess 是否登录成功
     * @return bool 是否允许继续操作
     */
    public function preventBruteForce($ipAddress, $username, $isSuccess = false)
    {
        // 如果功能未启用，直接返回true
        if (!$this->bruteForceConfig["enabled"]) {
            return true;
        }

        // 检查IP是否在黑名单中
        if ($this->isIpBlacklisted($ipAddress)) {
            $this->logSecurityEvent("brute_force_blocked", "阻止来自黑名单IP的访问", [
                "ip_address" => $ipAddress,
                "username" => $username
            ], 2);
            return false;
        }

        $cacheKey = "login_attempts:{$ipAddress}:{$username}";
        $attempts = Cache::get($cacheKey, 0);

        if ($isSuccess) {
            // 登录成功，重置尝试次数
            Cache::forget($cacheKey);
            return true;
        } else {
            // 登录失败，增加尝试次数
            $attempts++;
            Cache::put($cacheKey, $attempts, Carbon::now()->addMinutes($this->bruteForceConfig["detection_window"]));

            // 检查是否超过最大尝试次数
            if ($attempts >= $this->bruteForceConfig["max_failed_attempts"]) {
                // 锁定账户
                Cache::put("user_locked:{$username}", true, Carbon::now()->addMinutes($this->bruteForceConfig["lockout_time"]));
                
                // 记录安全事件
                $this->logSecurityEvent("brute_force_detected", "检测到暴力破解尝试", [
                    "ip_address" => $ipAddress,
                    "username" => $username,
                    "attempts" => $attempts
                ], 2);
                
                // 检查是否需要将IP加入黑名单
                $this->checkAndBlacklistIp($ipAddress);
                
                return false;
            }
        }

        return true;
    }

    /**
     * 检查IP是否在黑名单中
     *
     * @param string $ipAddress IP地址
     * @return bool
     */
    public function isIpBlacklisted($ipAddress)
    {
        if (!Schema::hasTable("database_ip_blacklist")) {
            return false;
        }

        return DB::table("database_ip_blacklist")
            ->where("ip_address", $ipAddress)
            ->where(function ($query) {
                $query->whereNull("expires_at")
                    ->orWhere("expires_at", ">", now());
            })
            ->exists();
    }

    /**
     * 检查并将IP加入黑名单
     *
     * @param string $ipAddress IP地址
     */
    protected function checkAndBlacklistIp($ipAddress)
    {
        if (!Schema::hasTable("database_security_logs") || !Schema::hasTable("database_ip_blacklist")) {
            return;
        }

        // 获取过去24小时内的失败尝试次数
        $failureCount = DB::table("database_security_logs")
            ->where("ip_address", $ipAddress)
            ->where("event_type", "brute_force_detected")
            ->where("created_at", ">=", now()->subHours(24))
            ->count();

        if ($failureCount >= $this->bruteForceConfig["ip_blacklist_threshold"]) {
            // 将IP加入黑名单
            DB::table("database_ip_blacklist")->insert([
                "ip_address" => $ipAddress,
                "reason" => "多次暴力破解尝试",
                "expires_at" => now()->addMinutes($this->bruteForceConfig["blacklist_duration"]),
                "created_at" => now(),
                "updated_at" => now()
            ]);

            $this->logSecurityEvent("ip_blacklisted", "IP已被加入黑名单", [
                "ip_address" => $ipAddress,
                "reason" => "多次暴力破解尝试",
                "expires_at" => now()->addMinutes($this->bruteForceConfig["blacklist_duration"])
            ], 2);
        }
    }

    /**
     * 检测SQL注入攻击
     *
     * @param string $query SQL查询
     * @param string $ipAddress IP地址
     * @param string|null $userId 用户ID
     * @return bool 是否允许执行查询
     */
    public function detectSqlInjection($query, $ipAddress, $userId = null)
    {
        // 如果功能未启用，直接返回true
        if (!$this->sqlInjectionConfig["enabled"]) {
            return true;
        }

        // SQL注入检测模式
        $patterns = Config::get("database_security.sql_injection.patterns", [
            "/\s*SELECT\s+.*\s+FROM\s+information_schema\./i",
            "/\s*SELECT\s+.*\s+FROM\s+mysql\./i",
            "/\s*UNION\s+SELECT\s+/i",
            "/\s*OR\s+1\s*=\s*1\s*/i",
            "/\s*OR\s+\'1\'\s*=\s*\'1\'\s*/i",
            "/\s*DROP\s+TABLE\s+/i",
            "/\s*DROP\s+DATABASE\s+/i",
            "/\s*DELETE\s+FROM\s+/i",
            "/\s*INSERT\s+INTO\s+.*\s+SELECT\s+/i",
            "/\s*SLEEP\s*\(/i",
            "/\s*BENCHMARK\s*\(/i",
            "/\s*LOAD_FILE\s*\(/i",
            "/\s*INTO\s+OUTFILE\s*/i",
            "/\s*INTO\s+DUMPFILE\s*/i",
        ]);

        $isSuspicious = false;
        $matchedPattern = null;

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $query)) {
                $isSuspicious = true;
                $matchedPattern = $pattern;
                break;
            }
        }

        if ($isSuspicious) {
            // 记录可疑查询
            if ($this->sqlInjectionConfig["log_suspicious_queries"] && Schema::hasTable("database_sql_injection_logs")) {
                DB::table("database_sql_injection_logs")->insert([
                    "query" => $query,
                    "pattern_matched" => $matchedPattern,
                    "ip_address" => $ipAddress,
                    "user_id" => $userId,
                    "was_blocked" => $this->sqlInjectionConfig["block_suspicious_queries"],
                    "created_at" => now(),
                    "updated_at" => now()
                ]);

                $this->logSecurityEvent("sql_injection_detected", "检测到疑似SQL注入尝试", [
                    "query" => $query,
                    "pattern_matched" => $matchedPattern,
                    "ip_address" => $ipAddress,
                    "user_id" => $userId
                ], 2);
            }

            // 检查是否需要将IP加入黑名单
            if (Schema::hasTable("database_sql_injection_logs")) {
                $attackCount = DB::table("database_sql_injection_logs")
                    ->where("ip_address", $ipAddress)
                    ->where("created_at", ">=", now()->subHours(1))
                    ->count();

                if ($attackCount >= $this->sqlInjectionConfig["alert_threshold"]) {
                    $this->checkAndBlacklistIp($ipAddress);
                }
            }

            // 是否阻止查询执行
            return !$this->sqlInjectionConfig["block_suspicious_queries"];
        }

        return true;
    }

    /**
     * 限制数据库连接数
     *
     * @param string $ipAddress IP地址
     * @return bool 是否允许新连接
     */
    public function limitConnections($ipAddress)
    {
        // 如果功能未启用，直接返回true
        if (!$this->connectionLimitConfig["enabled"]) {
            return true;
        }

        // 获取当前连接数
        $connections = DB::select("SHOW PROCESSLIST");
        
        // 统计每个IP的连接数
        $ipConnections = [];
        foreach ($connections as $connection) {
            $host = explode(":", $connection->Host)[0];
            if (!isset($ipConnections[$host])) {
                $ipConnections[$host] = 0;
            }
            $ipConnections[$host]++;
        }

        // 检查总连接数
        $totalConnections = count($connections);
        if ($totalConnections >= $this->connectionLimitConfig["max_connections_total"]) {
            $this->logSecurityEvent("connection_limit_exceeded", "超出总连接数限制", [
                "total_connections" => $totalConnections,
                "limit" => $this->connectionLimitConfig["max_connections_total"]
            ], 1);
            return false;
        }

        // 检查IP连接数
        $currentIpConnections = $ipConnections[$ipAddress] ?? 0;
        if ($currentIpConnections >= $this->connectionLimitConfig["max_connections_per_ip"]) {
            $this->logSecurityEvent("ip_connection_limit_exceeded", "超出IP连接数限制", [
                "ip_address" => $ipAddress,
                "connections" => $currentIpConnections,
                "limit" => $this->connectionLimitConfig["max_connections_per_ip"]
            ], 1);
            return false;
        }

        return true;
    }

    /**
     * 杀死长时间运行的查询
     */
    public function killLongRunningQueries()
    {
        // 如果功能未启用，直接返回
        if (!Config::get("database_security.monitoring.kill_long_queries", true)) {
            return;
        }

        // 获取所有进程
        $processes = DB::select("SHOW FULL PROCESSLIST");
        
        foreach ($processes as $process) {
            // 跳过系统进程
            if ($process->User === "system user" || $process->Command === "Daemon") {
                continue;
            }
            
            // 如果查询运行时间超过设定值，则终止它
            if ($process->Time > $this->connectionLimitConfig["connection_timeout"] && $process->Command === "Query") {
                try {
                    DB::statement("KILL {$process->Id}");
                    
                    $this->logSecurityEvent("killed_long_query", "终止长时间运行的查询", [
                        "process_id" => $process->Id,
                        "user" => $process->User,
                        "host" => $process->Host,
                        "db" => $process->db,
                        "command" => $process->Command,
                        "time" => $process->Time,
                        "state" => $process->State,
                        "info" => $process->Info
                    ], 1);
                } catch (\Exception $e) {
                    Log::error("终止查询失败", [
                        "process_id" => $process->Id,
                        "error" => $e->getMessage()
                    ]);
                }
            }
        }
    }

    /**
     * 监控数据库变化
     */
    public function monitorDatabaseChanges()
    {
        // 如果功能未启用，直接返回
        if (!Config::get("database_security.monitoring.monitor_changes", true)) {
            return;
        }

        // 获取所有表
        $tables = DB::select("SHOW TABLES");
        $tableNames = [];
        foreach ($tables as $table) {
            $tableNames[] = reset($table);
        }
        
        // 检查每个表的结构变化
        foreach ($tableNames as $table) {
            $cacheKey = "table_structure:{$table}";
            $currentStructure = $this->getTableStructureHash($table);
            $previousStructure = Cache::get($cacheKey);
            
            if ($previousStructure && $previousStructure !== $currentStructure) {
                $this->logSecurityEvent("table_structure_changed", "表结构发生变化", [
                    "table" => $table
                ], 1);
            }
            
            Cache::put($cacheKey, $currentStructure, Carbon::now()->addDays(30));
        }
    }

    /**
     * 获取表结构的哈希值
     *
     * @param string $table 表名
     * @return string 哈希值
     */
    protected function getTableStructureHash($table)
    {
        $structure = DB::select("SHOW CREATE TABLE `{$table}`")[0]->{"Create Table"};
        return md5($structure);
    }

    /**
     * 记录安全事件
     *
     * @param string $eventType 事件类型
     * @param string $description 事件描述
     * @param array $context 上下文信息
     * @param int $severity 严重程度 (0=信息, 1=警告, 2=危险)
     */
    public function logSecurityEvent($eventType, $description, $context = [], $severity = 0)
    {
        if (!Schema::hasTable("database_security_logs")) {
            Log::channel("security")->info($description, [
                "event_type" => $eventType,
                "ip_address" => request()->ip(),
                "user_id" => auth()->id(),
                "context" => $context,
                "severity" => $severity
            ]);
            return;
        }

        DB::table("database_security_logs")->insert([
            "event_type" => $eventType,
            "description" => $description,
            "ip_address" => request()->ip(),
            "user_agent" => request()->userAgent(),
            "user_id" => auth()->id(),
            "context" => json_encode($context),
            "severity" => $severity,
            "created_at" => now(),
            "updated_at" => now()
        ]);

        // 对于严重事件，同时写入系统日志
        if ($severity >= 2) {
            Log::channel("security")->error($description, [
                "event_type" => $eventType,
                "ip_address" => request()->ip(),
                "user_id" => auth()->id(),
                "context" => $context
            ]);
        }
    }

    /**
     * 创建数据库审计触发器
     */
    public function setupAuditTriggers()
    {
        // 如果功能未启用，直接返回
        if (!Config::get("database_security.audit.enabled", true)) {
            return;
        }

        if (!Schema::hasTable("database_audit_logs")) {
            return;
        }

        // 获取所有表
        $tables = DB::select("SHOW TABLES");
        $tableNames = [];
        foreach ($tables as $table) {
            $tableName = reset($table);
            
            // 跳过系统表和审计日志表
            if (in_array($tableName, ["database_audit_logs", "migrations", "database_security_logs", "database_ip_blacklist", "database_sql_injection_logs"])) {
                continue;
            }
            
            $tableNames[] = $tableName;
        }
        
        // 为每个表创建审计触发器
        foreach ($tableNames as $table) {
            $this->createTriggerForTable($table);
        }
    }
    
    /**
     * 为指定表创建审计触发器
     *
     * @param string $table 表名
     */
    protected function createTriggerForTable($table)
    {
        // 创建INSERT触发器
        DB::unprepared("
            DROP TRIGGER IF EXISTS `{$table}_after_insert`;
            CREATE TRIGGER `{$table}_after_insert` AFTER INSERT ON `{$table}` FOR EACH ROW
            BEGIN
                INSERT INTO database_audit_logs (table_name, action, user, ip_address, new_values, created_at, updated_at)
                VALUES ('{$table}', 'INSERT', USER(), @client_ip, JSON_OBJECT(
                    " . $this->getColumnsForTrigger($table, "NEW") . "
                ), NOW(), NOW());
            END;
        ");
        
        // 创建UPDATE触发器
        DB::unprepared("
            DROP TRIGGER IF EXISTS `{$table}_after_update`;
            CREATE TRIGGER `{$table}_after_update` AFTER UPDATE ON `{$table}` FOR EACH ROW
            BEGIN
                INSERT INTO database_audit_logs (table_name, action, user, ip_address, old_values, new_values, created_at, updated_at)
                VALUES ('{$table}', 'UPDATE', USER(), @client_ip, 
                JSON_OBJECT(
                    " . $this->getColumnsForTrigger($table, "OLD") . "
                ),
                JSON_OBJECT(
                    " . $this->getColumnsForTrigger($table, "NEW") . "
                ), NOW(), NOW());
            END;
        ");
        
        // 创建DELETE触发器
        DB::unprepared("
            DROP TRIGGER IF EXISTS `{$table}_after_delete`;
            CREATE TRIGGER `{$table}_after_delete` AFTER DELETE ON `{$table}` FOR EACH ROW
            BEGIN
                INSERT INTO database_audit_logs (table_name, action, user, ip_address, old_values, created_at, updated_at)
                VALUES ('{$table}', 'DELETE', USER(), @client_ip, JSON_OBJECT(
                    " . $this->getColumnsForTrigger($table, "OLD") . "
                ), NOW(), NOW());
            END;
        ");
    }
    
    /**
     * 获取表的列用于触发器
     *
     * @param string $table 表名
     * @param string $prefix 前缀 (NEW 或 OLD)
     * @return string 触发器中使用的列字符串
     */
    protected function getColumnsForTrigger($table, $prefix)
    {
        $columns = DB::select("SHOW COLUMNS FROM `{$table}`");
        $columnStrings = [];
        
        foreach ($columns as $column) {
            $columnName = $column->Field;
            $columnStrings[] = "'{$columnName}', {$prefix}.{$columnName}";
        }
        
        return implode(", ", $columnStrings);
    }

    /**
     * 创建数据库防火墙规则
     */
    public function setupDatabaseFirewall()
    {
        // 如果功能未启用，直接返回
        if (!Config::get("database_security.firewall.enabled", true)) {
            return;
        }

        if (!Schema::hasTable("database_firewall_rules")) {
            return;
        }
        
        // 添加默认规则
        $this->addDefaultFirewallRules();
    }
    
    /**
     * 添加默认防火墙规则
     */
    protected function addDefaultFirewallRules()
    {
        $defaultRules = Config::get("database_security.firewall.default_rules", [
            [
                "rule_type" => "QUERY_PATTERN",
                "rule_value" => "DROP DATABASE",
                "action" => "DENY",
                "description" => "阻止删除数据库操作"
            ],
            [
                "rule_type" => "QUERY_PATTERN",
                "rule_value" => "DROP TABLE",
                "action" => "DENY",
                "description" => "阻止删除表操作"
            ],
            [
                "rule_type" => "QUERY_PATTERN",
                "rule_value" => "TRUNCATE TABLE",
                "action" => "DENY",
                "description" => "阻止清空表操作"
            ],
            [
                "rule_type" => "QUERY_PATTERN",
                "rule_value" => "GRANT ALL",
                "action" => "DENY",
                "description" => "阻止授予所有权限"
            ]
        ]);
        
        foreach ($defaultRules as $rule) {
            // 检查规则是否已存在
            $exists = DB::table("database_firewall_rules")
                ->where("rule_type", $rule["rule_type"])
                ->where("rule_value", $rule["rule_value"])
                ->exists();
                
            if (!$exists) {
                DB::table("database_firewall_rules")->insert(array_merge($rule, [
                    "created_at" => now(),
                    "updated_at" => now()
                ]));
            }
        }
    }
    
    /**
     * 检查查询是否违反防火墙规则
     *
     * @param string $query SQL查询
     * @param string $user 数据库用户
     * @param string $ipAddress IP地址
     * @return bool 是否允许执行查询
     */
    public function checkFirewallRules($query, $user, $ipAddress)
    {
        // 如果功能未启用，直接返回true
        if (!Config::get("database_security.firewall.enabled", true)) {
            return true;
        }

        if (!Schema::hasTable("database_firewall_rules")) {
            return true;
        }

        // 检查IP规则
        $ipRule = DB::table("database_firewall_rules")
            ->where("rule_type", "IP")
            ->where("rule_value", $ipAddress)
            ->where("is_active", true)
            ->first();
            
        if ($ipRule && $ipRule->action === "DENY") {
            $this->logSecurityEvent("firewall_blocked", "防火墙阻止了来自IP的查询", [
                "ip_address" => $ipAddress,
                "query" => $query,
                "rule_id" => $ipRule->id
            ], 2);
            return false;
        }
        
        // 检查用户规则
        $userRule = DB::table("database_firewall_rules")
            ->where("rule_type", "USER")
            ->where("rule_value", $user)
            ->where("is_active", true)
            ->first();
            
        if ($userRule && $userRule->action === "DENY") {
            $this->logSecurityEvent("firewall_blocked", "防火墙阻止了来自用户的查询", [
                "user" => $user,
                "query" => $query,
                "rule_id" => $userRule->id
            ], 2);
            return false;
        }
        
        // 检查查询模式规则
        $patternRules = DB::table("database_firewall_rules")
            ->where("rule_type", "QUERY_PATTERN")
            ->where("is_active", true)
            ->get();
            
        foreach ($patternRules as $rule) {
            if (stripos($query, $rule->rule_value) !== false && $rule->action === "DENY") {
                $this->logSecurityEvent("firewall_blocked", "防火墙阻止了违反规则的查询", [
                    "pattern" => $rule->rule_value,
                    "query" => $query,
                    "rule_id" => $rule->id
                ], 2);
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 设置客户端IP变量
     * 在查询执行前调用此方法以便触发器可以使用客户端IP
     */
    public function setClientIpVariable()
    {
        $clientIp = request()->ip() ?: "未知";
        DB::statement("SET @client_ip = '{$clientIp}'");
    }
}
