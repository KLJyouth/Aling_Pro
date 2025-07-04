<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 创建会员等级表
        Schema::create('membership_levels', function (Blueprint \) {
            \->id();
            \->string('name');
            \->string('code')->unique();
            \->text('description')->nullable();
            \->decimal('price', 10, 2);
            \->integer('duration_days')->nullable();
            \->string('icon')->nullable();
            \->string('color')->nullable();
            \->json('benefits')->nullable();
            \->integer('api_quota')->default(0);
            \->integer('ai_quota')->default(0);
            \->integer('storage_quota')->default(0);
            \->integer('bandwidth_quota')->default(0);
            \->integer('discount_percent')->default(0);
            \->boolean('priority_support')->default(false);
            \->boolean('is_featured')->default(false);
            \->integer('sort_order')->default(0);
            \->string('status')->default('active'); // active, inactive
            \->timestamps();
            \->softDeletes();
        });

        // 创建会员订阅表
        Schema::create('membership_subscriptions', function (Blueprint \) {
            \->id();
            \->foreignId('user_id')->constrained()->onDelete('cascade');
            \->foreignId('membership_level_id')->nullable()->constrained()->nullOnDelete();
            \->foreignId('order_id')->nullable()->constrained('billing_orders')->nullOnDelete();
            \->string('subscription_no')->unique();
            \->timestamp('start_date');
            \->timestamp('end_date')->nullable();
            \->decimal('price_paid', 10, 2);
            \->boolean('auto_renew')->default(false);
            \->string('status')->default('active'); // active, expired, cancelled, pending
            \->timestamp('cancelled_at')->nullable();
            \->text('cancellation_reason')->nullable();
            \->timestamps();
            \->softDeletes();
        });

        // 在用户表中添加会员等级字段
        Schema::table('users', function (Blueprint \) {
            \->foreignId('membership_level_id')->nullable()->after('remember_token')->constrained('membership_levels')->nullOnDelete();
            \->timestamp('membership_expires_at')->nullable()->after('membership_level_id');
            \->boolean('is_premium')->default(false)->after('membership_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint \) {
            \->dropForeign(['membership_level_id']);
            \->dropColumn(['membership_level_id', 'membership_expires_at', 'is_premium']);
        });

        Schema::dropIfExists('membership_subscriptions');
        Schema::dropIfExists('membership_levels');
    }
};
