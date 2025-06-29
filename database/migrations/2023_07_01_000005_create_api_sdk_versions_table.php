<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiSdkVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_sdk_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_sdk_id')->constrained('api_sdks')->onDelete('cascade');
            $table->string('version');
            $table->string('file_path');
            $table->text('changelog')->nullable();
            $table->boolean('is_current')->default(false);
            $table->integer('download_count')->default(0);
            $table->timestamps();
            
            $table->unique(['api_sdk_id', 'version']);
            $table->index('is_current');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_sdk_versions');
    }
} 