<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsTablesSchema extends Migration
{
    /**
     * 运行迁移
     *
     * @return void
     */
    public function up()
    {
        // 创建新闻分类表
        Schema::create("news_categories", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("slug")->unique();
            $table->text("description")->nullable();
            $table->unsignedBigInteger("parent_id")->nullable();
            $table->integer("order")->default(0);
            $table->boolean("is_active")->default(true);
            $table->string("meta_keywords")->nullable();
            $table->string("meta_description")->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign("parent_id")->references("id")->on("news_categories")->onDelete("set null");
        });
        
        // 创建新闻标签表
        Schema::create("news_tags", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("slug")->unique();
            $table->text("description")->nullable();
            $table->boolean("is_active")->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
        
        // 创建新闻表
        Schema::create("news", function (Blueprint $table) {
            $table->id();
            $table->string("title");
            $table->string("slug")->unique();
            $table->longText("content");
            $table->text("summary")->nullable();
            $table->string("cover_image")->nullable();
            $table->unsignedBigInteger("author_id");
            $table->unsignedBigInteger("category_id")->nullable();
            $table->enum("status", ["draft", "published", "archived"])->default("draft");
            $table->timestamp("published_at")->nullable();
            $table->boolean("featured")->default(false);
            $table->integer("view_count")->default(0);
            $table->string("meta_keywords")->nullable();
            $table->string("meta_description")->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign("author_id")->references("id")->on("users")->onDelete("cascade");
            $table->foreign("category_id")->references("id")->on("news_categories")->onDelete("set null");
        });
        
        // 创建新闻标签关联表
        Schema::create("news_tag", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("news_id");
            $table->unsignedBigInteger("tag_id");
            $table->timestamps();
            
            $table->unique(["news_id", "tag_id"]);
            $table->foreign("news_id")->references("id")->on("news")->onDelete("cascade");
            $table->foreign("tag_id")->references("id")->on("news_tags")->onDelete("cascade");
        });
        
        // 创建新闻评论表
        Schema::create("news_comments", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("news_id");
            $table->unsignedBigInteger("user_id")->nullable();
            $table->unsignedBigInteger("parent_id")->nullable();
            $table->text("content");
            $table->enum("status", ["pending", "approved", "rejected"])->default("pending");
            $table->string("ip_address")->nullable();
            $table->string("user_agent")->nullable();
            $table->boolean("is_anonymous")->default(false);
            $table->string("author_name")->nullable();
            $table->string("author_email")->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign("news_id")->references("id")->on("news")->onDelete("cascade");
            $table->foreign("user_id")->references("id")->on("users")->onDelete("set null");
            $table->foreign("parent_id")->references("id")->on("news_comments")->onDelete("cascade");
        });
    }

    /**
     * 回滚迁移
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("news_comments");
        Schema::dropIfExists("news_tag");
        Schema::dropIfExists("news");
        Schema::dropIfExists("news_tags");
        Schema::dropIfExists("news_categories");
    }
}
