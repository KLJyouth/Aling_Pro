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
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('device_id');
            $table->string('device_name')->nullable();
            $table->string('device_type')->nullable(); // mobile, tablet, desktop
            $table->string('device_model')->nullable();
            $table->string('os_type')->nullable();
            $table->string('os_version')->nullable();
            $table->string('app_version')->nullable();
            $table->string('device_fingerprint')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('imei')->nullable();
            $table->string('mac_address')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['user_id', 'device_id']);
            $table->index('device_fingerprint');
            $table->index('last_active_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
