<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('security_threats', function (Blueprint $table) {
            $table->id();
            $table->string('threat_type')->index()->comment('威胁类型');
            $table->string('severity')->index()->comment('严重程度');
            $table->float('confidence')->default(0)->comment('置信度');
            $table->text('details')->nullable()->comment('详细信息');
            $table->string('source_ip')->nullable()->comment('源IP');
            $table->string('target')->nullable()->comment('目标');
            $table->string('status')->default('detected')->index()->comment('状态');
            $table->json('response_actions')->nullable()->comment('响应操作');
            $table->timestamp('resolved_at')->nullable()->comment('解决时间');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('security_threats');
    }
}; 