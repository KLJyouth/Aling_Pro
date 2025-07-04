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
        Schema::create('api_interfaces', function (Blueprint \) {
            \->id();
            \->string('name');
            \->string('path');
            \->string('method');
            \->string('group')->default('default');
            \->text('description')->nullable();
            \->string('status')->default('active');
            \->string('version')->default('1.0');
            \->integer('rate_limit')->nullable();
            \->boolean('auth_required')->default(true);
            \->integer('sort_order')->default(0);
            \->json('options')->nullable();
            \->timestamps();
        });

        Schema::create('api_interface_parameters', function (Blueprint \) {
            \->id();
            \->foreignId('api_interface_id')->constrained('api_interfaces')->onDelete('cascade');
            \->string('name');
            \->string('type');
            \->string('in');
            \->text('description')->nullable();
            \->boolean('required')->default(false);
            \->string('default')->nullable();
            \->string('example')->nullable();
            \->json('enum')->nullable();
            \->string('format')->nullable();
            \->string('pattern')->nullable();
            \->integer('min_length')->nullable();
            \->integer('max_length')->nullable();
            \->string('min_value')->nullable();
            \->string('max_value')->nullable();
            \->timestamps();
        });

        Schema::create('api_interface_responses', function (Blueprint \) {
            \->id();
            \->foreignId('api_interface_id')->constrained('api_interfaces')->onDelete('cascade');
            \->string('name');
            \->text('description')->nullable();
            \->integer('status_code');
            \->string('content_type')->default('application/json');
            \->json('schema')->nullable();
            \->text('example')->nullable();
            \->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_interface_responses');
        Schema::dropIfExists('api_interface_parameters');
        Schema::dropIfExists('api_interfaces');
    }
};
