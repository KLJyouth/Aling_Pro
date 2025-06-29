<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDatabaseManagerTables extends Migration
{
    /**
     * 运行迁移
     */
    public function up()
    {
        // 数据库备份记录表
        Schema::create('database_backups', function (Blueprint $table) {
            $table->id();
            $table->string('filename')->comment('备份文件名');
            $table->string('description')->nullable()->comment('备份描述');
            $table->unsignedBigInteger('size')->default(0)->comment('文件大小(字节)');
            $table->string('created_by')->nullable()->comment('创建者');
            $table->timestamps();
        });

        // 数据库查询日志表
        Schema::create('database_query_log', function (Blueprint $table) {
            $table->id();
            $table->text('query')->comment('查询语句');
            $table->float('execution_time')->default(0)->comment('执行时间(秒)');
            $table->integer('affected_rows')->default(0)->comment('影响行数');
            $table->boolean('is_select')->default(true)->comment('是否为查询操作');
            $table->unsignedBigInteger('user_id')->nullable()->comment('执行用户ID');
            $table->string('ip_address')->nullable()->comment('IP地址');
            $table->timestamps();
        });

        // 数据库优化记录表
        Schema::create('database_optimizations', function (Blueprint $table) {
            $table->id();
            $table->string('table_name')->comment('表名');
            $table->string('operation_type')->comment('操作类型：optimize, analyze, repair');
            $table->text('result')->nullable()->comment('操作结果');
            $table->boolean('success')->default(true)->comment('是否成功');
            $table->unsignedBigInteger('user_id')->nullable()->comment('执行用户ID');
            $table->timestamps();
        });

        // 数据库监控记录表
        Schema::create('database_monitors', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('connections')->default(0)->comment('当前连接数');
            $table->unsignedInteger('queries_per_second')->default(0)->comment('每秒查询数');
            $table->unsignedInteger('slow_queries')->default(0)->comment('慢查询数');
            $table->unsignedInteger('threads_running')->default(0)->comment('运行中的线程数');
            $table->unsignedInteger('threads_connected')->default(0)->comment('已连接的线程数');
            $table->unsignedInteger('aborted_clients')->default(0)->comment('中断的客户端数');
            $table->unsignedInteger('aborted_connects')->default(0)->comment('中断的连接数');
            $table->float('memory_usage')->default(0)->comment('内存使用率(%)');
            $table->float('cpu_usage')->default(0)->comment('CPU使用率(%)');
            $table->float('disk_usage')->default(0)->comment('磁盘使用率(%)');
            $table->timestamps();
        });

        // 慢查询分析表
        Schema::create('database_slow_queries', function (Blueprint $table) {
            $table->id();
            $table->text('query')->comment('查询语句');
            $table->float('query_time')->default(0)->comment('查询时间(秒)');
            $table->float('lock_time')->default(0)->comment('锁定时间(秒)');
            $table->unsignedInteger('rows_sent')->default(0)->comment('发送行数');
            $table->unsignedInteger('rows_examined')->default(0)->comment('扫描行数');
            $table->string('user_host')->nullable()->comment('用户@主机');
            $table->text('explain_result')->nullable()->comment('EXPLAIN结果');
            $table->text('suggestions')->nullable()->comment('优化建议');
            $table->timestamps();
        });

        // 数据库结构变更记录表
        Schema::create('database_structure_changes', function (Blueprint $table) {
            $table->id();
            $table->string('table_name')->comment('表名');
            $table->string('change_type')->comment('变更类型：create, alter, drop');
            $table->text('sql_statement')->comment('SQL语句');
            $table->text('before_state')->nullable()->comment('变更前状态');
            $table->text('after_state')->nullable()->comment('变更后状态');
            $table->unsignedBigInteger('user_id')->nullable()->comment('执行用户ID');
            $table->timestamps();
        });
    }

    /**
     * 回滚迁移
     */
    public function down()
    {
        Schema::dropIfExists('database_structure_changes');
        Schema::dropIfExists('database_slow_queries');
        Schema::dropIfExists('database_monitors');
        Schema::dropIfExists('database_optimizations');
        Schema::dropIfExists('database_query_log');
        Schema::dropIfExists('database_backups');
    }
} 