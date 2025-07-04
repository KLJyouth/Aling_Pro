<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 运行迁移
     *
     * @return void
     */
    public function up()
    {
        // 用户文件管理表
        Schema::create("user_files", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->string("name"); // 文件名称
            $table->string("path"); // 文件路径
            $table->string("type")->nullable(); // 文件类型
            $table->string("mime_type")->nullable(); // MIME类型
            $table->bigInteger("size")->default(0); // 文件大小（字节）
            $table->string("category")->nullable(); // 分类
            $table->text("description")->nullable(); // 描述
            $table->json("metadata")->nullable(); // 元数据
            $table->boolean("is_public")->default(false); // 是否公开
            $table->integer("download_count")->default(0); // 下载次数
            $table->timestamps();
            $table->softDeletes();
        });

        // 用户文件分类表
        Schema::create("user_file_categories", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->string("name"); // 分类名称
            $table->string("icon")->nullable(); // 图标
            $table->text("description")->nullable(); // 描述
            $table->integer("sort_order")->default(0); // 排序
            $table->timestamps();
            $table->softDeletes();
            
            // 索引
            $table->unique(["user_id", "name"]);
        });

        // 用户长期记忆表
        Schema::create("user_memories", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->string("key")->nullable(); // 记忆键
            $table->text("content"); // 记忆内容
            $table->string("type")->default("text"); // 记忆类型：text, json, embedding
            $table->string("category")->nullable(); // 分类
            $table->integer("importance")->default(5); // 重要性（1-10）
            $table->timestamp("last_accessed_at")->nullable(); // 最后访问时间
            $table->integer("access_count")->default(0); // 访问次数
            $table->json("metadata")->nullable(); // 元数据
            $table->timestamps();
            $table->softDeletes();
            
            // 索引
            $table->index(["user_id", "key"]);
            $table->index(["user_id", "category"]);
            $table->index(["user_id", "importance"]);
        });

        // 用户历史对话表
        Schema::create("user_conversations", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->string("title")->nullable(); // 对话标题
            $table->string("model")->nullable(); // 使用的模型
            $table->foreignId("agent_id")->nullable(); // 关联的智能体ID
            $table->json("system_prompt")->nullable(); // 系统提示词
            $table->json("metadata")->nullable(); // 元数据
            $table->timestamp("last_message_at")->nullable(); // 最后消息时间
            $table->integer("message_count")->default(0); // 消息数量
            $table->boolean("is_pinned")->default(false); // 是否置顶
            $table->boolean("is_archived")->default(false); // 是否归档
            $table->timestamps();
            $table->softDeletes();
            
            // 索引
            $table->index(["user_id", "last_message_at"]);
            $table->index(["user_id", "is_pinned"]);
        });

        // 对话消息表
        Schema::create("conversation_messages", function (Blueprint $table) {
            $table->id();
            $table->foreignId("conversation_id")->constrained("user_conversations")->onDelete("cascade");
            $table->string("role"); // 角色：user, assistant, system
            $table->text("content"); // 消息内容
            $table->json("metadata")->nullable(); // 元数据（如令牌计数、延迟等）
            $table->timestamps();
            
            // 索引
            $table->index("conversation_id");
        });

        // 用户认证表
        Schema::create("user_verifications", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->string("type"); // 认证类型：personal, business, team, government, education
            $table->string("status")->default("pending"); // 状态：pending, approved, rejected
            $table->string("name")->nullable(); // 认证名称（企业名称、团队名称等）
            $table->string("identifier")->nullable(); // 认证标识（如统一社会信用代码）
            $table->string("contact_name")->nullable(); // 联系人姓名
            $table->string("contact_phone")->nullable(); // 联系人电话
            $table->string("contact_email")->nullable(); // 联系人邮箱
            $table->text("description")->nullable(); // 描述
            $table->json("documents")->nullable(); // 认证文件
            $table->text("rejection_reason")->nullable(); // 拒绝原因
            $table->foreignId("verified_by")->nullable(); // 审核人ID
            $table->timestamp("verified_at")->nullable(); // 审核时间
            $table->timestamps();
            $table->softDeletes();
            
            // 索引
            $table->index(["user_id", "type"]);
            $table->index("status");
        });

        // 用户认证文件表
        Schema::create("verification_documents", function (Blueprint $table) {
            $table->id();
            $table->foreignId("verification_id")->constrained("user_verifications")->onDelete("cascade");
            $table->string("name"); // 文件名称
            $table->string("path"); // 文件路径
            $table->string("type"); // 文件类型：id_card, business_license, etc.
            $table->string("mime_type")->nullable(); // MIME类型
            $table->bigInteger("size")->default(0); // 文件大小（字节）
            $table->text("notes")->nullable(); // 备注
            $table->timestamps();
            
            // 索引
            $table->index("verification_id");
        });

        // 用户安全凭证表
        Schema::create("user_credentials", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->string("type"); // 凭证类型：totp, webauthn, recovery_code, trusted_device
            $table->string("identifier")->nullable(); // 凭证标识符
            $table->text("secret")->nullable(); // 密钥（加密存储）
            $table->json("metadata")->nullable(); // 元数据
            $table->boolean("is_primary")->default(false); // 是否为主要凭证
            $table->boolean("is_active")->default(true); // 是否激活
            $table->timestamp("last_used_at")->nullable(); // 最后使用时间
            $table->timestamps();
            $table->softDeletes();
            
            // 索引
            $table->index(["user_id", "type"]);
            $table->index(["user_id", "is_primary"]);
        });

        // 用户登录会话表
        Schema::create("user_sessions", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->string("session_id"); // 会话ID
            $table->string("ip_address")->nullable(); // IP地址
            $table->text("user_agent")->nullable(); // 用户代理
            $table->string("device_type")->nullable(); // 设备类型
            $table->string("location")->nullable(); // 位置
            $table->boolean("is_current")->default(false); // 是否为当前会话
            $table->timestamp("last_activity")->nullable(); // 最后活动时间
            $table->timestamps();
            
            // 索引
            $table->index("user_id");
            $table->index("session_id");
        });

        // 用户安全日志表
        Schema::create("user_security_logs", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->string("action"); // 操作：login, logout, 2fa_setup, password_change, etc.
            $table->string("status"); // 状态：success, failed
            $table->string("ip_address")->nullable(); // IP地址
            $table->text("user_agent")->nullable(); // 用户代理
            $table->string("device_type")->nullable(); // 设备类型
            $table->string("location")->nullable(); // 位置
            $table->text("details")->nullable(); // 详情
            $table->timestamps();
            
            // 索引
            $table->index("user_id");
            $table->index("action");
        });
    }

    /**
     * 回滚迁移
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("user_security_logs");
        Schema::dropIfExists("user_sessions");
        Schema::dropIfExists("user_credentials");
        Schema::dropIfExists("verification_documents");
        Schema::dropIfExists("user_verifications");
        Schema::dropIfExists("conversation_messages");
        Schema::dropIfExists("user_conversations");
        Schema::dropIfExists("user_memories");
        Schema::dropIfExists("user_file_categories");
        Schema::dropIfExists("user_files");
    }
};
