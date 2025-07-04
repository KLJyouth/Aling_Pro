<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberReferralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_referrals', function (Blueprint \) {
            \->id();
            \->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
            \->foreignId('referred_id')->constrained('users')->onDelete('cascade');
            \->string('code');
            \->string('status')->default('pending'); // pending, completed, rejected
            \->integer('points_awarded')->default(0);
            \->string('reward_type')->nullable(); // points, discount, extension, etc.
            \->decimal('reward_amount', 10, 2)->default(0);
            \->string('reward_description')->nullable();
            \->timestamps();
            
            // 添加索引
            \->index('referrer_id');
            \->index('referred_id');
            \->index('code');
            \->index('status');
        });

        // 添加推荐码字段到用户表
        Schema::table('users', function (Blueprint \) {
            \->string('referral_code')->nullable()->unique()->after('remember_token');
            \->integer('total_referrals')->default(0)->after('referral_code');
            \->integer('total_referral_points')->default(0)->after('total_referrals');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_referrals');
        
        Schema::table('users', function (Blueprint \) {
            \->dropColumn(['referral_code', 'total_referrals', 'total_referral_points']);
        });
    }
}
