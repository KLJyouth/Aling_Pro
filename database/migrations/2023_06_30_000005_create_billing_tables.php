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
        // 创建额度套餐表
        Schema::create('billing_packages', function (Blueprint \) {
            \->id();
            \->string('name');
            \->string('code')->unique();
            \->text('description')->nullable();
            \->string('type')->default('api'); // api, ai, storage, bandwidth, comprehensive
            \->integer('quota')->default(0);
            \->decimal('price', 10, 2);
            \->decimal('original_price', 10, 2)->nullable();
            \->integer('duration_days')->nullable();
            \->json('features')->nullable();
            \->boolean('is_popular')->default(false);
            \->boolean('is_recommended')->default(false);
            \->integer('sort_order')->default(0);
            \->string('status')->default('active'); // active, inactive, coming_soon
            \->timestamps();
            \->softDeletes();
        });

        // 创建商品表
        Schema::create('billing_products', function (Blueprint \) {
            \->id();
            \->foreignId('package_id')->nullable()->constrained('billing_packages')->nullOnDelete();
            \->string('name');
            \->string('code')->unique();
            \->text('description')->nullable();
            \->decimal('price', 10, 2);
            \->decimal('original_price', 10, 2)->nullable();
            \->string('image')->nullable();
            \->integer('stock')->default(0);
            \->integer('sales_count')->default(0);
            \->boolean('is_virtual')->default(true);
            \->boolean('is_featured')->default(false);
            \->boolean('is_limited')->default(false);
            \->integer('limited_stock')->nullable();
            \->timestamp('start_time')->nullable();
            \->timestamp('end_time')->nullable();
            \->integer('sort_order')->default(0);
            \->string('status')->default('active'); // active, inactive, soldout, coming_soon
            \->json('metadata')->nullable();
            \->timestamps();
            \->softDeletes();
        });

        // 创建订单表
        Schema::create('billing_orders', function (Blueprint \) {
            \->id();
            \->foreignId('user_id')->constrained()->onDelete('cascade');
            \->string('order_no')->unique();
            \->decimal('total_amount', 10, 2);
            \->string('payment_method')->nullable();
            \->string('payment_status')->default('pending'); // pending, paid, failed, refunded, partial_refunded
            \->string('transaction_id')->nullable();
            \->timestamp('paid_at')->nullable();
            \->string('status')->default('pending'); // pending, paid, processing, completed, cancelled, refunded, failed
            \->text('remark')->nullable();
            \->string('client_ip')->nullable();
            \->string('user_agent')->nullable();
            \->json('metadata')->nullable();
            \->timestamps();
            \->softDeletes();
        });

        // 创建订单项表
        Schema::create('billing_order_items', function (Blueprint \) {
            \->id();
            \->foreignId('order_id')->constrained('billing_orders')->onDelete('cascade');
            \->foreignId('product_id')->nullable()->constrained('billing_products')->nullOnDelete();
            \->string('product_name');
            \->string('product_code');
            \->integer('quantity');
            \->decimal('price', 10, 2);
            \->decimal('total', 10, 2);
            \->json('metadata')->nullable();
            \->timestamps();
        });

        // 创建用户套餐表
        Schema::create('user_packages', function (Blueprint \) {
            \->id();
            \->foreignId('user_id')->constrained()->onDelete('cascade');
            \->foreignId('package_id')->nullable()->constrained('billing_packages')->nullOnDelete();
            \->foreignId('order_id')->nullable()->constrained('billing_orders')->nullOnDelete();
            \->integer('quota_total');
            \->integer('quota_used')->default(0);
            \->integer('quota_remaining');
            \->timestamp('start_date');
            \->timestamp('end_date')->nullable();
            \->string('status')->default('active'); // active, expired, depleted, cancelled
            \->timestamps();
            \->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_packages');
        Schema::dropIfExists('billing_order_items');
        Schema::dropIfExists('billing_orders');
        Schema::dropIfExists('billing_products');
        Schema::dropIfExists('billing_packages');
    }
};
