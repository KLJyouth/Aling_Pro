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
     * �����ƹ�������
     */
    protected $bruteForceConfig;
    
    /**
     * SQLע���������
     */
    protected $sqlInjectionConfig;
    
    /**
     * ������������
     */
    protected $connectionLimitConfig;

    /**
     * ��ʼ�����ݿⰲȫ����
     */
    public function __construct()
    {
        // �������ļ���������
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
     * ��Ⲣ���������ƽ⹥��
     *
     * @param string $ipAddress IP��ַ
     * @param string $username �û���
     * @param bool $isSuccess �Ƿ��¼�ɹ�
     * @return bool �Ƿ������������
     */
    public function preventBruteForce($ipAddress, $username, $isSuccess = false)
    {
        // �������δ���ã�ֱ�ӷ���true
        if (!$this->bruteForceConfig["enabled"]) {
            return true;
        }

        // ���IP�Ƿ��ں�������
        if ($this->isIpBlacklisted($ipAddress)) {
            $this->logSecurityEvent("brute_force_blocked", "��ֹ���Ժ�����IP�ķ���", [
                "ip_address" => $ipAddress,
                "username" => $username
            ], 2);
            return false;
        }

        $cacheKey = "login_attempts:{$ipAddress}:{$username}";
        $attempts = Cache::get($cacheKey, 0);

        if ($isSuccess) {
            // ��¼�ɹ������ó��Դ���
            Cache::forget($cacheKey);
            return true;
        } else {
            // ��¼ʧ�ܣ����ӳ��Դ���
            $attempts++;
            Cache::put($cacheKey, $attempts, Carbon::now()->addMinutes($this->bruteForceConfig["detection_window"]));

            // ����Ƿ񳬹�����Դ���
            if ($attempts >= $this->bruteForceConfig["max_failed_attempts"]) {
                // �����˻�
                Cache::put("user_locked:{$username}", true, Carbon::now()->addMinutes($this->bruteForceConfig["lockout_time"]));
                
                // ��¼��ȫ�¼�
                $this->logSecurityEvent("brute_force_detected", "��⵽�����ƽⳢ��", [
                    "ip_address" => $ipAddress,
                    "username" => $username,
                    "attempts" => $attempts
                ], 2);
                
                // ����Ƿ���Ҫ��IP���������
                $this->checkAndBlacklistIp($ipAddress);
                
                return false;
            }
        }

        return true;
    }

    /**
     * ���IP�Ƿ��ں�������
     *
     * @param string $ipAddress IP��ַ
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
     * ��鲢��IP���������
     *
     * @param string $ipAddress IP��ַ
     */
    protected function checkAndBlacklistIp($ipAddress)
    {
        if (!Schema::hasTable("database_security_logs") || !Schema::hasTable("database_ip_blacklist")) {
            return;
        }

        // ��ȡ��ȥ24Сʱ�ڵ�ʧ�ܳ��Դ���
        $failureCount = DB::table("database_security_logs")
            ->where("ip_address", $ipAddress)
            ->where("event_type", "brute_force_detected")
            ->where("created_at", ">=", now()->subHours(24))
            ->count();

        if ($failureCount >= $this->bruteForceConfig["ip_blacklist_threshold"]) {
            // ��IP���������
            DB::table("database_ip_blacklist")->insert([
                "ip_address" => $ipAddress,
                "reason" => "��α����ƽⳢ��",
                "expires_at" => now()->addMinutes($this->bruteForceConfig["blacklist_duration"]),
                "created_at" => now(),
                "updated_at" => now()
            ]);

            $this->logSecurityEvent("ip_blacklisted", "IP�ѱ����������", [
                "ip_address" => $ipAddress,
                "reason" => "��α����ƽⳢ��",
                "expires_at" => now()->addMinutes($this->bruteForceConfig["blacklist_duration"])
            ], 2);
        }
    }

    /**
     * ���SQLע�빥��
     *
     * @param string $query SQL��ѯ
     * @param string $ipAddress IP��ַ
     * @param string|null $userId �û�ID
     * @return bool �Ƿ�����ִ�в�ѯ
     */
    public function detectSqlInjection($query, $ipAddress, $userId = null)
    {
        // �������δ���ã�ֱ�ӷ���true
        if (!$this->sqlInjectionConfig["enabled"]) {
            return true;
        }

        // SQLע����ģʽ
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
            // ��¼���ɲ�ѯ
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

                $this->logSecurityEvent("sql_injection_detected", "��⵽����SQLע�볢��", [
                    "query" => $query,
                    "pattern_matched" => $matchedPattern,
                    "ip_address" => $ipAddress,
                    "user_id" => $userId
                ], 2);
            }

            // ����Ƿ���Ҫ��IP���������
            if (Schema::hasTable("database_sql_injection_logs")) {
                $attackCount = DB::table("database_sql_injection_logs")
                    ->where("ip_address", $ipAddress)
                    ->where("created_at", ">=", now()->subHours(1))
                    ->count();

                if ($attackCount >= $this->sqlInjectionConfig["alert_threshold"]) {
                    $this->checkAndBlacklistIp($ipAddress);
                }
            }

            // �Ƿ���ֹ��ѯִ��
            return !$this->sqlInjectionConfig["block_suspicious_queries"];
        }

        return true;
    }

    /**
     * �������ݿ�������
     *
     * @param string $ipAddress IP��ַ
     * @return bool �Ƿ�����������
     */
    public function limitConnections($ipAddress)
    {
        // �������δ���ã�ֱ�ӷ���true
        if (!$this->connectionLimitConfig["enabled"]) {
            return true;
        }

        // ��ȡ��ǰ������
        $connections = DB::select("SHOW PROCESSLIST");
        
        // ͳ��ÿ��IP��������
        $ipConnections = [];
        foreach ($connections as $connection) {
            $host = explode(":", $connection->Host)[0];
            if (!isset($ipConnections[$host])) {
                $ipConnections[$host] = 0;
            }
            $ipConnections[$host]++;
        }

        // �����������
        $totalConnections = count($connections);
        if ($totalConnections >= $this->connectionLimitConfig["max_connections_total"]) {
            $this->logSecurityEvent("connection_limit_exceeded", "����������������", [
                "total_connections" => $totalConnections,
                "limit" => $this->connectionLimitConfig["max_connections_total"]
            ], 1);
            return false;
        }

        // ���IP������
        $currentIpConnections = $ipConnections[$ipAddress] ?? 0;
        if ($currentIpConnections >= $this->connectionLimitConfig["max_connections_per_ip"]) {
            $this->logSecurityEvent("ip_connection_limit_exceeded", "����IP����������", [
                "ip_address" => $ipAddress,
                "connections" => $currentIpConnections,
                "limit" => $this->connectionLimitConfig["max_connections_per_ip"]
            ], 1);
            return false;
        }

        return true;
    }

    /**
     * ɱ����ʱ�����еĲ�ѯ
     */
    public function killLongRunningQueries()
    {
        // �������δ���ã�ֱ�ӷ���
        if (!Config::get("database_security.monitoring.kill_long_queries", true)) {
            return;
        }

        // ��ȡ���н���
        $processes = DB::select("SHOW FULL PROCESSLIST");
        
        foreach ($processes as $process) {
            // ����ϵͳ����
            if ($process->User === "system user" || $process->Command === "Daemon") {
                continue;
            }
            
            // �����ѯ����ʱ�䳬���趨ֵ������ֹ��
            if ($process->Time > $this->connectionLimitConfig["connection_timeout"] && $process->Command === "Query") {
                try {
                    DB::statement("KILL {$process->Id}");
                    
                    $this->logSecurityEvent("killed_long_query", "��ֹ��ʱ�����еĲ�ѯ", [
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
                    Log::error("��ֹ��ѯʧ��", [
                        "process_id" => $process->Id,
                        "error" => $e->getMessage()
                    ]);
                }
            }
        }
    }

    /**
     * ������ݿ�仯
     */
    public function monitorDatabaseChanges()
    {
        // �������δ���ã�ֱ�ӷ���
        if (!Config::get("database_security.monitoring.monitor_changes", true)) {
            return;
        }

        // ��ȡ���б�
        $tables = DB::select("SHOW TABLES");
        $tableNames = [];
        foreach ($tables as $table) {
            $tableNames[] = reset($table);
        }
        
        // ���ÿ����Ľṹ�仯
        foreach ($tableNames as $table) {
            $cacheKey = "table_structure:{$table}";
            $currentStructure = $this->getTableStructureHash($table);
            $previousStructure = Cache::get($cacheKey);
            
            if ($previousStructure && $previousStructure !== $currentStructure) {
                $this->logSecurityEvent("table_structure_changed", "��ṹ�����仯", [
                    "table" => $table
                ], 1);
            }
            
            Cache::put($cacheKey, $currentStructure, Carbon::now()->addDays(30));
        }
    }

    /**
     * ��ȡ��ṹ�Ĺ�ϣֵ
     *
     * @param string $table ����
     * @return string ��ϣֵ
     */
    protected function getTableStructureHash($table)
    {
        $structure = DB::select("SHOW CREATE TABLE `{$table}`")[0]->{"Create Table"};
        return md5($structure);
    }

    /**
     * ��¼��ȫ�¼�
     *
     * @param string $eventType �¼�����
     * @param string $description �¼�����
     * @param array $context ��������Ϣ
     * @param int $severity ���س̶� (0=��Ϣ, 1=����, 2=Σ��)
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

        // ���������¼���ͬʱд��ϵͳ��־
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
     * �������ݿ���ƴ�����
     */
    public function setupAuditTriggers()
    {
        // �������δ���ã�ֱ�ӷ���
        if (!Config::get("database_security.audit.enabled", true)) {
            return;
        }

        if (!Schema::hasTable("database_audit_logs")) {
            return;
        }

        // ��ȡ���б�
        $tables = DB::select("SHOW TABLES");
        $tableNames = [];
        foreach ($tables as $table) {
            $tableName = reset($table);
            
            // ����ϵͳ��������־��
            if (in_array($tableName, ["database_audit_logs", "migrations", "database_security_logs", "database_ip_blacklist", "database_sql_injection_logs"])) {
                continue;
            }
            
            $tableNames[] = $tableName;
        }
        
        // Ϊÿ��������ƴ�����
        foreach ($tableNames as $table) {
            $this->createTriggerForTable($table);
        }
    }
    
    /**
     * Ϊָ��������ƴ�����
     *
     * @param string $table ����
     */
    protected function createTriggerForTable($table)
    {
        // ����INSERT������
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
        
        // ����UPDATE������
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
        
        // ����DELETE������
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
     * ��ȡ��������ڴ�����
     *
     * @param string $table ����
     * @param string $prefix ǰ׺ (NEW �� OLD)
     * @return string ��������ʹ�õ����ַ���
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
     * �������ݿ����ǽ����
     */
    public function setupDatabaseFirewall()
    {
        // �������δ���ã�ֱ�ӷ���
        if (!Config::get("database_security.firewall.enabled", true)) {
            return;
        }

        if (!Schema::hasTable("database_firewall_rules")) {
            return;
        }
        
        // ���Ĭ�Ϲ���
        $this->addDefaultFirewallRules();
    }
    
    /**
     * ���Ĭ�Ϸ���ǽ����
     */
    protected function addDefaultFirewallRules()
    {
        $defaultRules = Config::get("database_security.firewall.default_rules", [
            [
                "rule_type" => "QUERY_PATTERN",
                "rule_value" => "DROP DATABASE",
                "action" => "DENY",
                "description" => "��ֹɾ�����ݿ����"
            ],
            [
                "rule_type" => "QUERY_PATTERN",
                "rule_value" => "DROP TABLE",
                "action" => "DENY",
                "description" => "��ֹɾ�������"
            ],
            [
                "rule_type" => "QUERY_PATTERN",
                "rule_value" => "TRUNCATE TABLE",
                "action" => "DENY",
                "description" => "��ֹ��ձ����"
            ],
            [
                "rule_type" => "QUERY_PATTERN",
                "rule_value" => "GRANT ALL",
                "action" => "DENY",
                "description" => "��ֹ��������Ȩ��"
            ]
        ]);
        
        foreach ($defaultRules as $rule) {
            // �������Ƿ��Ѵ���
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
     * ����ѯ�Ƿ�Υ������ǽ����
     *
     * @param string $query SQL��ѯ
     * @param string $user ���ݿ��û�
     * @param string $ipAddress IP��ַ
     * @return bool �Ƿ�����ִ�в�ѯ
     */
    public function checkFirewallRules($query, $user, $ipAddress)
    {
        // �������δ���ã�ֱ�ӷ���true
        if (!Config::get("database_security.firewall.enabled", true)) {
            return true;
        }

        if (!Schema::hasTable("database_firewall_rules")) {
            return true;
        }

        // ���IP����
        $ipRule = DB::table("database_firewall_rules")
            ->where("rule_type", "IP")
            ->where("rule_value", $ipAddress)
            ->where("is_active", true)
            ->first();
            
        if ($ipRule && $ipRule->action === "DENY") {
            $this->logSecurityEvent("firewall_blocked", "����ǽ��ֹ������IP�Ĳ�ѯ", [
                "ip_address" => $ipAddress,
                "query" => $query,
                "rule_id" => $ipRule->id
            ], 2);
            return false;
        }
        
        // ����û�����
        $userRule = DB::table("database_firewall_rules")
            ->where("rule_type", "USER")
            ->where("rule_value", $user)
            ->where("is_active", true)
            ->first();
            
        if ($userRule && $userRule->action === "DENY") {
            $this->logSecurityEvent("firewall_blocked", "����ǽ��ֹ�������û��Ĳ�ѯ", [
                "user" => $user,
                "query" => $query,
                "rule_id" => $userRule->id
            ], 2);
            return false;
        }
        
        // ����ѯģʽ����
        $patternRules = DB::table("database_firewall_rules")
            ->where("rule_type", "QUERY_PATTERN")
            ->where("is_active", true)
            ->get();
            
        foreach ($patternRules as $rule) {
            if (stripos($query, $rule->rule_value) !== false && $rule->action === "DENY") {
                $this->logSecurityEvent("firewall_blocked", "����ǽ��ֹ��Υ������Ĳ�ѯ", [
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
     * ���ÿͻ���IP����
     * �ڲ�ѯִ��ǰ���ô˷����Ա㴥��������ʹ�ÿͻ���IP
     */
    public function setClientIpVariable()
    {
        $clientIp = request()->ip() ?: "δ֪";
        DB::statement("SET @client_ip = '{$clientIp}'");
    }
}
