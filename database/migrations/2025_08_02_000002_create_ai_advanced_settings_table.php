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
        Schema::create("ai_advanced_settings", function (Blueprint $table) {
            $table->id();
            $table->string("key")->unique(); // 设置键
            $table->text("value")->nullable(); // 设置值
            $table->string("group")->default("general"); // 设置组
            $table->text("description")->nullable(); // 描述
            $table->timestamps();
            
            // 索引
            $table->index("group");
        });
        
        // 插入默认设置
        DB::table("ai_advanced_settings")->insert([
            // API密钥轮换设置
            [
                "key" => "enable_api_key_rotation",
                "value" => "0",
                "group" => "api_keys",
                "description" => "启用API密钥轮换，系统会自动轮换使用同一提供商的多个API密钥",
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "key" => "rotation_strategy",
                "value" => "round_robin",
                "group" => "api_keys",
                "description" => "轮换策略：round_robin（轮询）, random（随机）, weighted（加权）",
                "created_at" => now(),
                "updated_at" => now()
            ],
            
            // 负载均衡设置
            [
                "key" => "enable_load_balancing",
                "value" => "0",
                "group" => "api_keys",
                "description" => "启用负载均衡，系统会根据负载情况分配请求",
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "key" => "load_balancing_strategy",
                "value" => "least_used",
                "group" => "api_keys",
                "description" => "负载均衡策略：least_used（最少使用）, percentage（百分比）",
                "created_at" => now(),
                "updated_at" => now()
            ],
            
            // 请求缓存设置
            [
                "key" => "enable_request_caching",
                "value" => "0",
                "group" => "caching",
                "description" => "启用请求缓存，系统会缓存相同请求的响应",
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "key" => "request_cache_ttl",
                "value" => "60",
                "group" => "caching",
                "description" => "请求缓存有效期（分钟）",
                "created_at" => now(),
                "updated_at" => now()
            ],
            
            // 故障转移设置
            [
                "key" => "enable_fallback",
                "value" => "0",
                "group" => "fallback",
                "description" => "启用故障转移，当主要提供商API调用失败时，会自动尝试使用备用提供商",
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "key" => "fallback_provider",
                "value" => "",
                "group" => "fallback",
                "description" => "备用AI提供商ID",
                "created_at" => now(),
                "updated_at" => now()
            ],
            
            // 日志和审计设置
            [
                "key" => "enable_detailed_logging",
                "value" => "1",
                "group" => "logging",
                "description" => "启用详细日志记录",
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "key" => "log_retention_days",
                "value" => "90",
                "group" => "logging",
                "description" => "日志保留天数",
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "key" => "enable_audit_logging",
                "value" => "1",
                "group" => "logging",
                "description" => "启用审计日志记录",
                "created_at" => now(),
                "updated_at" => now()
            ]
        ]);
    }

    /**
     * 回滚迁移
     * 
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("ai_advanced_settings");
    }
};
