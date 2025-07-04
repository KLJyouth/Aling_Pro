<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberPrivilegesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_privileges', function (Blueprint \) {
            \->id();
            \->string('name');
            \->string('code')->unique();
            \->string('description')->nullable();
            \->string('icon')->nullable();
            \->string('status')->default('active');
            \->boolean('is_featured')->default(false);
            \->integer('sort_order')->default(0);
            \->timestamps();
            
            // 添加索引
            \->index('code');
            \->index('status');
        });

        // 创建会员特权与会员等级的中间表
        Schema::create('member_privilege_level', function (Blueprint \) {
            \->id();
            \->foreignId('privilege_id')->constrained('member_privileges')->onDelete('cascade');
            \->foreignId('level_id')->constrained('membership_levels')->onDelete('cascade');
            \->string('value')->nullable();
            \->timestamps();
            
            // 添加唯一约束
            \->unique(['privilege_id', 'level_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_privilege_level');
        Schema::dropIfExists('member_privileges');
    }
}
