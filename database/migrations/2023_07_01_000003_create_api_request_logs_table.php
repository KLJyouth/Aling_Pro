<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiRequestLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_key_id')->nullable()->constrained('api_keys');
            $table->foreignId('api_interface_id')->nullable()->constrained('api_interfaces');
            $table->string('method', 10);
            $table->string('path');
            $table->json('query_params')->nullable();
            $table->json('request_body')->nullable();
            $table->json('response_body')->nullable();
            $table->integer('status_code');
            $table->integer('response_time')->comment('响应时间（毫秒）');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['api_key_id', 'created_at']);
            $table->index(['api_interface_id', 'created_at']);
            $table->index(['status_code', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_request_logs');
    }
} 