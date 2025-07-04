<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 创建通知系统相关表
 */
class CreateNotificationsTables extends Migration
{
    /**
     * 运行迁移
     *
     * @return void
     */
    public function up()
    {
        // 创建通知模板表
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');                      // 模板名称
            $table->string('code')->unique();            // 模板代码（唯一标识）
            $table->string('type');                      // 模板类型: system, email, api
            $table->string('subject')->nullable();       // 邮件主题（用于邮件模板）
            $table->text('content');                     // 模板内容
            $table->text('html_content')->nullable();    // HTML内容（用于邮件模板）
            $table->text('description')->nullable();     // 模板描述
            $table->json('variables')->nullable();       // 模板变量（JSON）
            $table->string('status')->default('active'); // 状态: active, inactive
            $table->unsignedBigInteger('creator_id')->nullable(); // 创建者ID
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });

        // 创建通知表
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');                     // 通知标题
            $table->text('content');                     // 通知内容
            $table->string('type');                      // 通知类型: system(系统通知), user(用户通知), email(邮件通知), api(API通知)
            $table->string('status')->default('draft');  // 状态: draft(草稿), sending(发送中), sent(已发送), failed(发送失败)
            $table->string('priority')->default('normal'); // 优先级: low, normal, high, urgent
            $table->unsignedBigInteger('sender_id')->nullable(); // 发送者ID
            $table->unsignedBigInteger('template_id')->nullable(); // 模板ID
            $table->timestamp('scheduled_at')->nullable(); // 计划发送时间
            $table->timestamp('sent_at')->nullable();    // 实际发送时间
            $table->json('metadata')->nullable();        // 元数据(JSON)
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('template_id')->references('id')->on('notification_templates')->onDelete('set null');
        });

        // 创建通知接收者表
        Schema::create('notification_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_id'); // 通知ID
            $table->unsignedBigInteger('user_id')->nullable(); // 用户ID（可为空，用于外部接收者）
            $table->string('email')->nullable();         // 邮箱地址（用于邮件通知）
            $table->string('phone')->nullable();         // 手机号码（用于短信通知）
            $table->string('api_endpoint')->nullable();  // API端点（用于API通知）
            $table->string('status')->default('pending'); // 状态：pending(待处理), sent(已发送), read(已读), failed(发送失败)
            $table->timestamp('sent_at')->nullable();    // 发送时间
            $table->timestamp('read_at')->nullable();    // 阅读时间
            $table->text('error_message')->nullable();   // 错误信息
            $table->timestamps();
            
            $table->foreign('notification_id')->references('id')->on('notifications')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            // 添加索引
            $table->index(['notification_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('email');
        });

        // 创建通知附件表
        Schema::create('notification_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_id'); // 通知ID
            $table->string('file_name');                // 文件名
            $table->string('file_path');                // 文件路径
            $table->unsignedBigInteger('file_size');    // 文件大小（字节）
            $table->string('file_type');                // 文件类型（MIME类型）
            $table->string('description')->nullable();   // 文件描述
            $table->timestamps();
            
            $table->foreign('notification_id')->references('id')->on('notifications')->onDelete('cascade');
        });

        // 创建邮件发送接口表
        Schema::create('email_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');                      // 接口名称
            $table->string('provider_type');             // 接口类型: smtp, sendgrid, mailgun, ses, etc.
            $table->string('host')->nullable();          // SMTP主机
            $table->integer('port')->nullable();         // SMTP端口
            $table->string('username')->nullable();      // 用户名/账号
            $table->string('password')->nullable();      // 密码/密钥
            $table->string('encryption')->nullable();    // 加密方式: tls, ssl, null
            $table->string('api_key')->nullable();       // API密钥（用于API类型的邮件服务）
            $table->string('api_secret')->nullable();    // API密钥（用于API类型的邮件服务）
            $table->string('region')->nullable();        // 区域（用于AWS SES等）
            $table->string('from_email');                // 默认发件人邮箱
            $table->string('from_name')->nullable();     // 默认发件人名称
            $table->string('reply_to_email')->nullable(); // 默认回复邮箱
            $table->string('status')->default('active'); // 状态: active, inactive
            $table->boolean('is_default')->default(false); // 是否为默认接口
            $table->integer('daily_limit')->nullable();  // 每日发送限制
            $table->unsignedBigInteger('creator_id')->nullable(); // 创建者ID
            $table->json('settings')->nullable();        // 其他设置（JSON）
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * 回滚迁移
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_attachments');
        Schema::dropIfExists('notification_recipients');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('email_providers');
    }
}
