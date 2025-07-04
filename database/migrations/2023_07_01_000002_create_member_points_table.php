<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_points', function (Blueprint \) {
            \->id();
            \->foreignId('user_id')->constrained()->onDelete('cascade');
            \->integer('points');
            \->string('action');
            \->string('description')->nullable();
            \->string('reference_id')->nullable();
            \->string('reference_type')->nullable();
            \->timestamp('expires_at')->nullable();
            \->timestamps();
            
            // 添加索引
            \->index('user_id');
            \->index('action');
            \->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_points');
    }
}
