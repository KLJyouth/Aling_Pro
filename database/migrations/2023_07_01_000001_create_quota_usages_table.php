<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuotaUsagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quota_usages', function (Blueprint \) {
            \->id();
            \->foreignId('user_id')->constrained()->onDelete('cascade');
            \->string('quota_type'); // api, ai, storage, bandwidth
            \->integer('amount');
            \->string('description')->nullable();
            \->timestamp('used_at');
            \->timestamps();
            
            // 添加索引
            \->index(['user_id', 'quota_type']);
            \->index('used_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quota_usages');
    }
}
