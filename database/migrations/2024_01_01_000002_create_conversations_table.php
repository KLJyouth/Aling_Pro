<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConversationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->enum('type', ['chat', 'document', 'code', 'creative', 'analysis'])->default('chat');
            $table->enum('status', ['active', 'archived', 'paused', 'completed'])->default('active');
            $table->json('context')->nullable();
            $table->json('messages')->nullable();
            $table->json('metadata')->nullable();
            $table->json('settings')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->boolean('is_public')->default(false);
            $table->string('share_token')->nullable()->unique();
            $table->integer('view_count')->default(0);
            $table->tinyInteger('rating')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // 索引
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'is_favorite']);
            $table->index('is_public');
            $table->index('share_token');
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
}
