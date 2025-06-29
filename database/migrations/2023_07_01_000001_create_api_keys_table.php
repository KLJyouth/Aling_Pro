<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('name');
            $table->string('key_prefix', 16);
            $table->string('key_hash', 64);
            $table->timestamp('expiration_date')->nullable();
            $table->integer('rate_limit')->nullable();
            $table->string('ip_restrictions')->nullable();
            $table->json('permissions')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('key_prefix');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_keys');
    }
} 