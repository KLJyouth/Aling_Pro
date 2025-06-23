<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('filename')->nullable();
            $table->string('original_filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->string('file_path')->nullable();
            $table->enum('type', ['text', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'image', 'video', 'audio', 'other'])->default('text');
            $table->enum('status', ['uploading', 'processing', 'active', 'archived', 'failed'])->default('active');
            $table->longText('content')->nullable();
            $table->json('metadata')->nullable();
            $table->json('tags')->nullable();
            $table->json('analysis_result')->nullable();
            $table->boolean('is_public')->default(false);
            $table->string('share_token')->nullable()->unique();
            $table->integer('view_count')->default(0);
            $table->integer('download_count')->default(0);
            $table->tinyInteger('rating')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // 索引
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'type']);
            $table->index('is_public');
            $table->index('share_token');
            $table->index('mime_type');
            $table->index('created_at');
            $table->index('updated_at');
            $table->fullText(['title', 'description', 'content']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
}
