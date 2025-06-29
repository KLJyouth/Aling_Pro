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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('has_mfa')->default(false)->after('remember_token');
            $table->json('mfa_recovery_codes')->nullable()->after('has_mfa');
            $table->timestamp('last_login_at')->nullable()->after('mfa_recovery_codes');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->string('phone_number')->nullable()->after('email');
            $table->timestamp('phone_verified_at')->nullable()->after('phone_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'has_mfa',
                'mfa_recovery_codes',
                'last_login_at',
                'last_login_ip',
                'phone_number',
                'phone_verified_at',
            ]);
        });
    }
};
