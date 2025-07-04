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
        // 用户活跃度统计表
        Schema::create(\
user_activity_stats\, function (Blueprint \) {
            \->id();
            \->foreignId(\user_id\)->constrained()->onDelete(\cascade\);
            \->date(\date\);
            \->integer(\login_count\)->default(0); // 登录次数
            \->integer(\file_operations\)->default(0); // 文件操作次数
            \->integer(\memory_operations\)->default(0); // 记忆操作次数
            \->integer(\conversation_count\)->default(0); // 对话数量
            \->integer(\message_count\)->default(0); // 消息数量
            \->integer(\api_calls\)->default(0); // API调用次数
            \->integer(\total_actions\)->default(0); // 总操作次数
            \->timestamps();
            
            // 索引
            \->unique([\user_id\, \date\]);
            \->index(\date\);
        });

        // 用户资源使用统计表
        Schema::create(\user_resource_stats\, function (Blueprint \) {
            \->id();
            \->foreignId(\user_id\)->constrained()->onDelete(\cascade\);
            \->date(\date\);
            \->bigInteger(\storage_used\)->default(0); // 存储使用量（字节）
            \->integer(\files_count\)->default(0); // 文件数量
            \->integer(\memories_count\)->default(0); // 记忆数量
            \->integer(\conversations_count\)->default(0); // 对话数量
            \->bigInteger(\tokens_used\)->default(0); // 令牌使用量
            \->timestamps();
            
            // 索引
            \->unique([\user_id\, \date\]);
            \->index(\date\);
        });

        // 用户行为分析表
        Schema::create(\user_behavior_analytics\, function (Blueprint \) {
            \->id();
            \->foreignId(\user_id\)->constrained()->onDelete(\cascade\);
            \->string(\feature_used\); // 使用的功能
            \->string(\action_type\); // 操作类型
            \->json(\context\)->nullable(); // 上下文信息
            \->string(\user_agent\)->nullable(); // 用户代理
            \->string(\ip_address\)->nullable(); // IP地址
            \->string(\device_type\)->nullable(); // 设备类型
            \->timestamps();
            
            // 索引
            \->index(\user_id\);
            \->index(\feature_used\);
            \->index(\action_type\);
            \->index(\created_at\);
        });

        // 用户增长统计表
        Schema::create(\user_growth_stats\, function (Blueprint \) {
            \->id();
            \->date(\date\)->unique();
            \->integer(\new_users\)->default(0); // 新用户数
            \->integer(\active_users\)->default(0); // 活跃用户数
            \->integer(\returning_users\)->default(0); // 回访用户数
            \->integer(\churned_users\)->default(0); // 流失用户数
            \->integer(\verified_users\)->default(0); // 已验证用户数
            \->float(\retention_rate\)->nullable(); // 留存率
            \->float(\churn_rate\)->nullable(); // 流失率
            \->timestamps();
            
            // 索引
            \->index(\date\);
        });
    }

    /**
     * 回滚迁移
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(\user_growth_stats\);
        Schema::dropIfExists(\user_behavior_analytics\);
        Schema::dropIfExists(\user_resource_stats\);
        Schema::dropIfExists(\user_activity_stats\);
    }
};
