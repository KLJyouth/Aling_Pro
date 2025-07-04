<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMcpTables extends Migration
{
    /**
     * 运行迁移
     *
     * @return void
     */
    public function up()
    {
        // 创建MCP接口表
        Schema::create("mcp_interfaces", function (Blueprint $table) {
            $table->id();
            $table->string("name", 100)->comment("接口名称");
            $table->string("endpoint", 255)->comment("接口端点");
            $table->text("description")->nullable()->comment("接口描述");
            $table->string("method", 10)->comment("请求方法");
            $table->json("parameters")->nullable()->comment("参数定义");
            $table->json("response_format")->nullable()->comment("响应格式");
            $table->boolean("is_active")->default(true)->comment("是否激活");
            $table->boolean("requires_auth")->default(true)->comment("是否需要认证");
            $table->integer("rate_limit")->default(60)->comment("速率限制(每分钟)");
            $table->timestamps();
            
            $table->unique(["endpoint", "method"]);
        });

        // 创建MCP日志表
        Schema::create("mcp_logs", function (Blueprint $table) {
            $table->id();
            $table->foreignId("interface_id")->nullable()->comment("接口ID")
                  ->constrained("mcp_interfaces")->nullOnDelete();
            $table->string("method", 10)->comment("请求方法");
            $table->string("endpoint", 255)->comment("接口端点");
            $table->json("request_data")->nullable()->comment("请求数据");
            $table->integer("status_code")->comment("状态码");
            $table->json("response_data")->nullable()->comment("响应数据");
            $table->float("response_time")->nullable()->comment("响应时间(毫秒)");
            $table->string("ip_address", 45)->nullable()->comment("IP地址");
            $table->text("user_agent")->nullable()->comment("用户代理");
            $table->foreignId("user_id")->nullable()->comment("用户ID")
                  ->constrained("users")->nullOnDelete();
            $table->timestamp("created_at")->useCurrent()->comment("创建时间");
            
            $table->index("created_at");
            $table->index("endpoint");
            $table->index("status_code");
        });

        // 创建MCP配置表
        Schema::create("mcp_configs", function (Blueprint $table) {
            $table->id();
            $table->string("key", 100)->unique()->comment("配置键");
            $table->text("value")->nullable()->comment("配置值");
            $table->string("group", 50)->default("general")->comment("配置组");
            $table->text("description")->nullable()->comment("配置描述");
            $table->boolean("is_system")->default(false)->comment("是否系统配置");
            $table->timestamps();
            
            $table->index("group");
        });
    }

    /**
     * 回滚迁移
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("mcp_logs");
        Schema::dropIfExists("mcp_interfaces");
        Schema::dropIfExists("mcp_configs");
    }
}
