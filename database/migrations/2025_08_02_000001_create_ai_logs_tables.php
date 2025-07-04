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
        Schema::create("ai_api_logs", function (Blueprint $table) {
            $table->id();
            $table->foreignId("provider_id")->nullable()->constrained("ai_model_providers")->nullOnDelete();
            $table->foreignId("model_id")->nullable()->constrained("ai_models")->nullOnDelete();
            $table->foreignId("agent_id")->nullable()->constrained("ai_agents")->nullOnDelete();
            $table->foreignId("api_key_id")->nullable()->constrained("ai_api_keys")->nullOnDelete();
            $table->foreignId("user_id")->nullable()->constrained("users")->nullOnDelete();
            $table->string("request_id")->nullable(); // 请求唯一ID
            $table->string("ip_address")->nullable(); // 请求IP
            $table->string("endpoint")->nullable(); // 请求端点
            $table->text("request_data")->nullable(); // 请求数据
            $table->text("response_data")->nullable(); // 响应数据
            $table->integer("response_time")->default(0); // 响应时间（毫秒）
            $table->integer("input_tokens")->default(0); // 输入标记数
            $table->integer("output_tokens")->default(0); // 输出标记数
            $table->string("status")->default("pending"); // 状态：pending, success, error
            $table->text("error_message")->nullable(); // 错误信息
            $table->decimal("cost", 10, 6)->default(0); // 调用成本
            $table->string("session_id")->nullable(); // 会话ID
            $table->timestamps();
            
            // 索引
            $table->index("request_id");
            $table->index("ip_address");
            $table->index("status");
            $table->index("created_at");
            $table->index("session_id");
        });

        Schema::create("ai_audit_logs", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->nullable()->constrained("users")->nullOnDelete();
            $table->string("action"); // 操作类型：create, update, delete, view
            $table->string("resource_type"); // 资源类型：provider, model, agent, api_key, setting
            $table->unsignedBigInteger("resource_id")->nullable(); // 资源ID
            $table->text("old_values")->nullable(); // 旧值（JSON）
            $table->text("new_values")->nullable(); // 新值（JSON）
            $table->string("ip_address")->nullable(); // 操作IP
            $table->text("user_agent")->nullable(); // 用户代理
            $table->timestamps();
            
            // 索引
            $table->index("action");
            $table->index("resource_type");
            $table->index(["resource_type", "resource_id"]);
            $table->index("created_at");
        });
    }

    /**
     * 回滚迁移
     * 
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("ai_audit_logs");
        Schema::dropIfExists("ai_api_logs");
    }
};
