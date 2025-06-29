<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDatabaseSecurityTables extends Migration
{
    /**
     * 运行迁移
     *
     * @return void
     */
    public function up()
    {
        // 创建数据库安全日志表
        Schema::create('database_security_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->text('description');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('user_id')->nullable();
            $table->json('context')->nullable();
            $table->integer('severity')->default(0); // 0=信息, 1=警告, 2=危险
            $table->timestamps();
            
            $table->index(['event_type', 'severity']);
            $table->index('created_at');
        });

        // 创建IP黑名单表
        Schema::create('database_ip_blacklist', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address');
            $table->text('reason');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index('ip_address');
            $table->index('expires_at');
        });

        // 创建SQL注入检测日志表
        Schema::create('database_sql_injection_logs', function (Blueprint $table) {
            $table->id();
            $table->text('query');
            $table->string('pattern_matched')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_id')->nullable();
            $table->boolean('was_blocked')->default(false);
            $table->timestamps();
            
            $table->index('ip_address');
            $table->index('created_at');
        });
        
        // 创建数据库审计日志表
        Schema::create('database_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('table_name');
            $table->string('action'); // INSERT, UPDATE, DELETE
            $table->string('user');
            $table->string('ip_address')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamps();
            
            $table->index(['table_name', 'action']);
            $table->index('created_at');
        });
        
        // 创建数据库防火墙规则表
        Schema::create('database_firewall_rules', function (Blueprint $table) {
            $table->id();
            $table->string('rule_type'); // IP, USER, QUERY_PATTERN
            $table->string('rule_value');
            $table->string('action'); // ALLOW, DENY
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['rule_type', 'rule_value']);
            $table->index('is_active');
        });
        
        // 创建数据库访问日志表
        Schema::create('database_access_logs', function (Blueprint $table) {
            $table->id();
            $table->string('user');
            $table->string('ip_address');
            $table->string('database_name');
            $table->string('action_type'); // CONNECT, DISCONNECT, QUERY
            $table->text('query')->nullable();
            $table->float('execution_time')->nullable();
            $table->integer('affected_rows')->nullable();
            $table->timestamps();
            
            $table->index(['user', 'action_type']);
            $table->index('ip_address');
            $table->index('created_at');
        });
        
        // 创建数据库性能监控表
        Schema::create('database_performance_logs', function (Blueprint $table) {
            $table->id();
            $table->float('cpu_usage')->nullable();
            $table->float('memory_usage')->nullable();
            $table->integer('active_connections')->nullable();
            $table->integer('queries_per_second')->nullable();
            $table->integer('slow_queries')->nullable();
            $table->float('disk_usage')->nullable();
            $table->json('additional_metrics')->nullable();
            $table->timestamps();
            
            $table->index('created_at');
        });
        
        // 创建数据库漏洞扫描结果表
        Schema::create('database_vulnerability_scans', function (Blueprint $table) {
            $table->id();
            $table->string('scan_type');
            $table->dateTime('scan_started_at');
            $table->dateTime('scan_completed_at')->nullable();
            $table->string('status'); // PENDING, RUNNING, COMPLETED, FAILED
            $table->integer('vulnerabilities_found')->default(0);
            $table->integer('critical_count')->default(0);
            $table->integer('high_count')->default(0);
            $table->integer('medium_count')->default(0);
            $table->integer('low_count')->default(0);
            $table->json('scan_results')->nullable();
            $table->text('summary')->nullable();
            $table->timestamps();
            
            $table->index('scan_type');
            $table->index('status');
            $table->index('created_at');
        });
        
        // 创建数据库漏洞详情表
        Schema::create('database_vulnerabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_id')->constrained('database_vulnerability_scans')->onDelete('cascade');
            $table->string('vulnerability_type');
            $table->string('severity'); // CRITICAL, HIGH, MEDIUM, LOW
            $table->string('affected_object')->nullable();
            $table->text('description');
            $table->text('recommendation')->nullable();
            $table->boolean('is_fixed')->default(false);
            $table->dateTime('fixed_at')->nullable();
            $table->string('fixed_by')->nullable();
            $table->timestamps();
            
            $table->index('vulnerability_type');
            $table->index('severity');
            $table->index('is_fixed');
        });
        
        // 创建数据库配置审计表
        Schema::create('database_config_audits', function (Blueprint $table) {
            $table->id();
            $table->string('config_name');
            $table->string('previous_value')->nullable();
            $table->string('new_value')->nullable();
            $table->string('changed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('config_name');
            $table->index('created_at');
        });
        
        // 创建数据库备份监控表
        Schema::create('database_backup_monitors', function (Blueprint $table) {
            $table->id();
            $table->string('backup_name');
            $table->string('backup_type'); // FULL, DIFFERENTIAL, INCREMENTAL
            $table->dateTime('started_at');
            $table->dateTime('completed_at')->nullable();
            $table->string('status'); // PENDING, RUNNING, COMPLETED, FAILED
            $table->float('size_mb')->nullable();
            $table->string('storage_location')->nullable();
            $table->boolean('is_compressed')->default(false);
            $table->boolean('is_encrypted')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index('backup_type');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * 回滚迁移
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('database_backup_monitors');
        Schema::dropIfExists('database_config_audits');
        Schema::dropIfExists('database_vulnerabilities');
        Schema::dropIfExists('database_vulnerability_scans');
        Schema::dropIfExists('database_performance_logs');
        Schema::dropIfExists('database_access_logs');
        Schema::dropIfExists('database_firewall_rules');
        Schema::dropIfExists('database_audit_logs');
        Schema::dropIfExists('database_sql_injection_logs');
        Schema::dropIfExists('database_ip_blacklist');
        Schema::dropIfExists('database_security_logs');
    }
}
