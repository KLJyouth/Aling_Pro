<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonitoringAlertsTable extends Migration
{
    /**
     * 运行迁移
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monitoring_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('告警类型：performance, health, application, security');
            $table->string('level')->comment('告警级别：critical, warning, info');
            $table->string('source')->comment('告警来源：cpu, memory, disk, database, cache, storage, queue, api, etc.');
            $table->string('message')->comment('告警消息');
            $table->json('details')->nullable()->comment('告警详情');
            $table->string('status')->default('active')->comment('告警状态：active, acknowledged, resolved');
            $table->integer('occurrence_count')->default(1)->comment('发生次数');
            $table->timestamp('first_occurred_at')->nullable()->comment('首次发生时间');
            $table->timestamp('last_occurred_at')->nullable()->comment('最近发生时间');
            $table->unsignedBigInteger('acknowledged_by')->nullable()->comment('确认人ID');
            $table->timestamp('acknowledged_at')->nullable()->comment('确认时间');
            $table->unsignedBigInteger('resolved_by')->nullable()->comment('解决人ID');
            $table->timestamp('resolved_at')->nullable()->comment('解决时间');
            $table->text('comment')->nullable()->comment('备注');
            $table->text('resolution')->nullable()->comment('解决方案');
            $table->timestamps();
            $table->softDeletes();
            
            // 外键约束
            $table->foreign('acknowledged_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
            
            // 索引
            $table->index('type');
            $table->index('level');
            $table->index('source');
            $table->index('status');
            $table->index('last_occurred_at');
        });
    }

    /**
     * 回滚迁移
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('monitoring_alerts');
    }
} 