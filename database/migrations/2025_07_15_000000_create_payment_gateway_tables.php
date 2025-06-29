<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePaymentGatewayTables extends Migration
{
    /**
     * 运行迁移
     *
     * @return void
     */
    public function up()
    {
        // 支付网关表
        Schema::create("payment_gateways", function (Blueprint $table) {
            $table->id();
            $table->string("name"); // 支付网关名称
            $table->string("code")->unique(); // 支付网关代码，如alipay, wechat, paypal
            $table->string("description")->nullable(); // 描述
            $table->text("config"); // 配置信息，JSON格式存储
            $table->boolean("is_active")->default(false); // 是否启用
            $table->boolean("is_test_mode")->default(false); // 是否为测试模式
            $table->string("logo")->nullable(); // 支付网关logo
            $table->integer("sort_order")->default(0); // 排序
            $table->timestamps();
            $table->softDeletes();

            $table->index("code");
            $table->index("is_active");
        });

        // 支付交易记录表
        Schema::create("payment_transactions", function (Blueprint $table) {
            $table->id();
            $table->string("transaction_id")->unique(); // 交易ID
            $table->foreignId("gateway_id")->constrained("payment_gateways"); // 支付网关ID
            $table->string("order_id"); // 订单ID
            $table->string("user_id")->nullable(); // 用户ID
            $table->decimal("amount", 10, 2); // 金额
            $table->string("currency", 10)->default("CNY"); // 货币
            $table->string("status"); // 状态：pending, completed, failed, refunded
            $table->text("gateway_response")->nullable(); // 支付网关返回的原始数据
            $table->string("payment_method")->nullable(); // 支付方式
            $table->string("client_ip")->nullable(); // 客户端IP
            $table->string("error_message")->nullable(); // 错误信息
            $table->timestamp("paid_at")->nullable(); // 支付时间
            $table->timestamps();
            $table->softDeletes();

            $table->index("transaction_id");
            $table->index("order_id");
            $table->index("user_id");
            $table->index("status");
            $table->index("paid_at");
        });

        // 退款记录表
        Schema::create("payment_refunds", function (Blueprint $table) {
            $table->id();
            $table->string("refund_id")->unique(); // 退款ID
            $table->foreignId("transaction_id")->constrained("payment_transactions"); // 交易ID
            $table->decimal("amount", 10, 2); // 退款金额
            $table->string("status"); // 状态：pending, completed, failed
            $table->string("reason")->nullable(); // 退款原因
            $table->text("gateway_response")->nullable(); // 支付网关返回的原始数据
            $table->string("operator")->nullable(); // 操作人
            $table->timestamp("refunded_at")->nullable(); // 退款时间
            $table->timestamps();
            $table->softDeletes();

            $table->index("refund_id");
            $table->index("status");
        });

        // 支付网关日志表
        Schema::create("payment_gateway_logs", function (Blueprint $table) {
            $table->id();
            $table->foreignId("gateway_id")->constrained("payment_gateways"); // 支付网关ID
            $table->string("transaction_id")->nullable(); // 交易ID
            $table->string("action"); // 操作类型：payment, refund, notification, etc.
            $table->text("request")->nullable(); // 请求数据
            $table->text("response")->nullable(); // 响应数据
            $table->string("ip_address")->nullable(); // IP地址
            $table->string("user_agent")->nullable(); // 用户代理
            $table->boolean("is_success")->default(false); // 是否成功
            $table->string("error_message")->nullable(); // 错误信息
            $table->timestamps();

            $table->index("gateway_id");
            $table->index("transaction_id");
            $table->index("action");
            $table->index("is_success");
            $table->index("created_at");
        });

        // 支付设置表
        Schema::create("payment_settings", function (Blueprint $table) {
            $table->id();
            $table->string("key")->unique(); // 设置键
            $table->text("value")->nullable(); // 设置值
            $table->string("group")->default("general"); // 分组
            $table->string("description")->nullable(); // 描述
            $table->boolean("is_system")->default(false); // 是否为系统设置
            $table->timestamps();

            $table->index("key");
            $table->index("group");
        });

        // 插入默认设置
        DB::table("payment_settings")->insert([
            [
                "key" => "payment_currency",
                "value" => "CNY",
                "group" => "general",
                "description" => "默认支付货币",
                "is_system" => true,
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "key" => "payment_expire_time",
                "value" => "30", // 单位：分钟
                "group" => "general",
                "description" => "支付过期时间（分钟）",
                "is_system" => true,
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "key" => "auto_complete_payment",
                "value" => "true",
                "group" => "general",
                "description" => "自动完成支付",
                "is_system" => true,
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "key" => "payment_notification_email",
                "value" => "admin@example.com",
                "group" => "notification",
                "description" => "支付通知邮箱",
                "is_system" => true,
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "key" => "payment_success_template",
                "value" => "您的订单 {order_id} 已支付成功，金额：{amount}",
                "group" => "notification",
                "description" => "支付成功通知模板",
                "is_system" => true,
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "key" => "payment_failed_template",
                "value" => "您的订单 {order_id} 支付失败，原因：{reason}",
                "group" => "notification",
                "description" => "支付失败通知模板",
                "is_system" => true,
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
        Schema::dropIfExists("payment_settings");
        Schema::dropIfExists("payment_gateway_logs");
        Schema::dropIfExists("payment_refunds");
        Schema::dropIfExists("payment_transactions");
        Schema::dropIfExists("payment_gateways");
    }
}
