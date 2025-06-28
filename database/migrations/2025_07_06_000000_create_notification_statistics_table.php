<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建通知统计表
 */
class CreateNotificationStatisticsTable extends Migration
{
    /**
     * 运行迁移
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_statistics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_id');     // 通知ID
            $table->date('date');                             // 统计日期
            $table->integer('sent_count')->default(0);        // 发送数量
            $table->integer('delivered_count')->default(0);   // 送达数量
            $table->integer('read_count')->default(0);        // 阅读数量
            $table->integer('failed_count')->default(0);      // 失败数量
            $table->integer('click_count')->default(0);       // 点击数量
            $table->json('device_stats')->nullable();         // 设备统计（JSON）
            $table->json('location_stats')->nullable();       // 位置统计（JSON）
            $table->json('browser_stats')->nullable();        // 浏览器统计（JSON）
            $table->json('os_stats')->nullable();             // 操作系统统计（JSON）
            $table->json('time_stats')->nullable();           // 时间段统计（JSON）
            $table->json('metadata')->nullable();             // 其他元数据（JSON）
            $table->timestamps();
            
            // 外键约束
            $table->foreign('notification_id')->references('id')->on('notifications')->onDelete('cascade');
            
            // 添加索引
            $table->index(['notification_id', 'date']);
            $table->index('date');
        });

        // 创建通知自动规则表
        Schema::create('notification_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // 规则名称
            $table->text('description')->nullable();         // 规则描述
            $table->string('event_type');                    // 触发事件类型
            $table->json('conditions')->nullable();          // 触发条件（JSON）
            $table->unsignedBigInteger('template_id')->nullable(); // 使用的模板ID
            $table->json('recipients')->nullable();          // 接收者配置（JSON）
            $table->json('settings')->nullable();            // 规则设置（JSON）
            $table->boolean('is_active')->default(true);     // 是否激活
            $table->unsignedBigInteger('creator_id')->nullable(); // 创建者ID
            $table->timestamps();
            $table->softDeletes();
            
            // 外键约束
            $table->foreign('template_id')->references('id')->on('notification_templates')->onDelete('set null');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });

        // 创建通知跟踪表
        Schema::create('notification_tracking', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_id');   // 通知ID
            $table->unsignedBigInteger('recipient_id');      // 接收者ID
            $table->string('tracking_type');                 // 跟踪类型: open, click, bounce, etc.
            $table->string('tracking_id')->unique();         // 跟踪ID（唯一）
            $table->string('ip_address')->nullable();        // IP地址
            $table->string('user_agent')->nullable();        // 用户代理
            $table->json('device_info')->nullable();         // 设备信息（JSON）
            $table->json('location_info')->nullable();       // 位置信息（JSON）
            $table->string('url')->nullable();               // 点击的URL
            $table->timestamp('tracked_at');                 // 跟踪时间
            $table->json('metadata')->nullable();            // 其他元数据（JSON）
            $table->timestamps();
            
            // 外键约束
            $table->foreign('notification_id')->references('id')->on('notifications')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('notification_recipients')->onDelete('cascade');
            
            // 添加索引
            $table->index(['notification_id', 'tracking_type']);
            $table->index(['recipient_id', 'tracking_type']);
            $table->index('tracked_at');
        });
    }

    /**
     * 回滚迁移
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_tracking');
        Schema::dropIfExists('notification_rules');
        Schema::dropIfExists('notification_statistics');
    }
} 